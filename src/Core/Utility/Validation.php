<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-08-10
     * Project: Good Food Tracker - API
     */

    namespace Gac\GoodFoodTracker\Core\Utility;

    use Gac\GoodFoodTracker\Core\Exceptions\Validation\FieldsDoNotMatchException;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidEmailException;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidNumericValueException;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\MaximumLengthException;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\MinimumLengthException;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
    use Gac\Routing\Request;
    use Ramsey\Uuid\Uuid;

    class Validation
    {
        /**
         * Validation method used to run all the validation based on the specified criteria
         *
         * @param array $validation_fields List of filed from the request body to be validated
         * @param Request $request Instance of a Request class containing all the request body data
         *
         * @throws FieldsDoNotMatchException Throws an exception if the validation fails its criteria
         * @throws InvalidEmailException Throws an exception if the validation fails its criteria
         * @throws InvalidNumericValueException Throws an exception if the validation fails its criteria
         * @throws InvalidUUIDException Throws an exception if the validation fails its criteria
         * @throws MaximumLengthException Throws an exception if the validation fails its criteria
         * @throws MinimumLengthException Throws an exception if the validation fails its criteria
         * @throws RequiredFieldException Throws an exception if the validation fails its criteria
         *
         * @return bool Returns true if the validation was successful
         */
        public static function validate(array $validation_fields, Request $request): bool
        {
            foreach ($validation_fields as $field => $rules) {
                $ruleCondition = null;

                foreach ($rules as $rule) {
                    if (is_array($rule)) {
                        $ruleCondition = $rule[array_key_first($rule)];
                        $rule = array_key_first($rule);
                    }

                    switch ($rule) {
                        case ValidationRules::REQUIRED:
                            self::required_value($field, $request);
                            break;

                        case ValidationRules::MIN_LENGTH:
                            self::min_length($field, $request, $ruleCondition);
                            break;

                        case ValidationRules::MAX_LENGTH:
                            self::max_length($field, $request, $ruleCondition);
                            break;

                        case ValidationRules::VALID_EMAIL:
                            self::valid_email($field, $request);
                            break;

                        case ValidationRules::NUMERIC:
                            self::numeric($field, $request);
                            break;

                        case ValidationRules::VALID_UUID:
                            self::valid_uuid($field, $ruleCondition, $request);
                            break;

                        case ValidationRules::SAME_AS:
                            self::same_as($field, $ruleCondition, $request);
                            break;
                    }
                }
            }

            return true;
        }

        /**
         * Validation method used for checking if the value is present in the request and not empty
         *
         * @param string $field Which field in the request to validate
         * @param Request $request Instance of a Request class containing all the request body data
         *
         * @throws RequiredFieldException Throws an exception if the validation fails its criteria
         */
        private static function required_value(string $field, Request $request): void
        {
            $value = $request->get($field);

            if (is_string($value) && empty($value)) {
                throw new RequiredFieldException($field);
            } elseif (is_null($value)) {
                throw new RequiredFieldException($field);
            }
        }

        /**
         * Validation method used for checking if the value meets the minimum length specified
         *
         * @param string $field Which field in the request to validate
         * @param Request $request Instance of a Request class containing all the request body data
         * @param int $minLength Minimum needed value
         *
         * @throws MinimumLengthException Throws an exception if the validation fails its criteria
         */
        private static function min_length(string $field, Request $request, int $minLength = 0): void
        {
            $value = $request->get($field);
            if (mb_strlen($value) < $minLength) {
                throw new MinimumLengthException($minLength, $field);
            }
        }

        /**
         * Validation method used for checking if the value meets the maximum length specified
         *
         * @param string $field Which field in the request to validate
         * @param Request $request Instance of a Request class containing all the request body data
         * @param int $maxLength Maximum needed value
         *
         * @throws MaximumLengthException Throws an exception if the validation fails its criteria
         */
        private static function max_length(string $field, Request $request, int $maxLength = 0): void
        {
            $value = $request->get($field);

            if (mb_strlen($value) > $maxLength) {
                throw new MaximumLengthException($maxLength, $field);
            }
        }

        /**
         * Validation method used for checking if the provided value is a valid email address
         *
         * @param string $field Which field in the request to validate
         * @param Request $request Instance of a Request class containing all the request body data
         *
         * @throws InvalidEmailException Throws an exception if the validation fails its criteria
         */
        private static function valid_email(string $field, Request $request): void
        {
            $value = $request->get($field);

            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidEmailException();
            }
        }

        /**
         * Validation method used for checking if the provided value is numeric
         *
         * @param string $field Which field in the request to validate
         * @param Request $request Instance of a Request class containing all the request body data
         *
         * @throws InvalidNumericValueException Throws an exception if the validation fails its criteria
         */
        private static function numeric(string $field, Request $request): void
        {
            $value = $request->get($field);

            if (!is_numeric($value)) {
                throw new InvalidNumericValueException($value, $field);
            }
        }

        /**
         * Validation method used for validation if the provided is a valid UUID value
         *
         * @param string $field Which field in the request to validate
         * @param mixed $ruleCondition
         * @param Request $request Instance of a Request class containing all the request body data
         *
         * @throws InvalidUUIDException Throws an exception if the validation fails its criteria
         */
        private static function valid_uuid(string $field, mixed $ruleCondition, Request $request): void
        {
            $value = $request->get($field) ?? $ruleCondition;
            if (!Uuid::isValid($value)) {
                throw new InvalidUUIDException();
            }
        }

        /**
         * Validation method used for validating if the 2 fields in the request body have the same value
         *
         * @param string $field Which field in the request to compare the value with
         * @param string $compareField Which field in the request to compare the value with
         * @param Request $request Instance of a Request class containing all the request body data
         *
         * @throws FieldsDoNotMatchException Throws an exception if the validation fails its criteria
         */
        private static function same_as(string $field, string $compareField, Request $request): void
        {
            $value = $request->get($field);
            $valueToCompare = $request->get($compareField);

            if ($value !== $valueToCompare) {
                throw new FieldsDoNotMatchException($field, $compareField);
            }
        }
    }
