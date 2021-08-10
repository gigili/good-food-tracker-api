<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Utility;


	use Gac\GoodFoodTracker\exceptions\validation\FieldsDoNotMatchException;
	use Gac\GoodFoodTracker\exceptions\validation\InvalidEmailException;
	use Gac\GoodFoodTracker\exceptions\validation\InvalidNumericValueException;
	use Gac\GoodFoodTracker\exceptions\validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\exceptions\validation\MaximumLengthException;
	use Gac\GoodFoodTracker\exceptions\validation\MinimumLengthException;
	use Gac\GoodFoodTracker\exceptions\validation\RequiredFieldException;
	use Gac\Routing\Request;
	use Ramsey\Uuid\Uuid;

	/**
	 * Class Validation
	 *
	 * @package Gac\GoodFoodTracker\Utility
	 *
	 */
	class Validation
	{

		/**
		 * @param array $validation_fields
		 * @param Request $request
		 *
		 * @throws FieldsDoNotMatchException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws InvalidUUIDException
		 * @throws MaximumLengthException
		 * @throws MinimumLengthException
		 * @throws RequiredFieldException
		 * @return bool
		 */
		public static function validate(array $validation_fields, Request $request) : bool {
			foreach ( $validation_fields as $field => $rules ) {
				$ruleCondition = NULL;

				foreach ( $rules as $rule ) {
					if ( is_array($rule) ) {
						$ruleCondition = $rule[array_key_first($rule)];
						$rule = array_key_first($rule);
					}

					switch ( $rule ) {
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
		 *
		 * @param string $field
		 * @param Request $request
		 *
		 * @throws RequiredFieldException if the value for the provided field is missing
		 */
		private static function required_value(string $field, Request $request) {
			$value = $request->get($field);

			if ( is_string($value) && empty($value) ) {
				throw new RequiredFieldException($field);
			} elseif ( is_null($value) ) {
				throw new RequiredFieldException($field);
			}
		}

		/**
		 * @param string $field
		 * @param Request $request *
		 * @param int $minLength
		 *
		 * @throws MinimumLengthException
		 */
		private static function min_length(string $field, Request $request, int $minLength = 0) {
			$value = $request->get($field);
			if ( mb_strlen($value) < $minLength ) {
				throw new MinimumLengthException($minLength, $field);
			}
		}

		/**
		 * @param string $field
		 * @param Request $request *
		 * @param int $maxLength
		 *
		 * @throws MaximumLengthException
		 */
		private static function max_length(string $field, Request $request, int $maxLength = 0) {
			$value = $request->get($field);

			if ( mb_strlen($value) > $maxLength ) {
				throw new MaximumLengthException($maxLength, $field);
			}
		}

		/**
		 * @param string $field
		 * @param Request $request *
		 *
		 * @throws InvalidEmailException
		 */
		private static function valid_email(string $field, Request $request) {
			$value = $request->get($field);

			if ( !filter_var($value, FILTER_VALIDATE_EMAIL) ) {
				throw new InvalidEmailException();
			}
		}

		/**
		 * @param string $field
		 * @param Request $request *
		 *
		 * @throws InvalidNumericValueException
		 */
		private static function numeric(string $field, Request $request) {
			$value = $request->get($field);

			if ( !is_numeric($value) ) {
				throw new InvalidNumericValueException($value, $field);
			}
		}

		/**
		 * @param string $field
		 * @param mixed $ruleCondition
		 * @param Request $request *
		 *
		 * @throws InvalidUUIDException
		 */
		private static function valid_uuid(string $field, mixed $ruleCondition, Request $request) {
			$value = $request->get($field) ?? $ruleCondition;
			if ( !Uuid::isValid($value) ) {
				throw new InvalidUUIDException();
			}
		}

		/**
		 * @throws FieldsDoNotMatchException
		 */
		private static function same_as(string $field, string $compareField, Request $request) {
			$value = $request->get($field);
			$valueToCompare = $request->get($compareField);

			if ( $value !== $valueToCompare ) {
				throw new FieldsDoNotMatchException($field, $compareField);
			}
		}
	}


