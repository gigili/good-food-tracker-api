<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-08-10
     * Project: Good Food Tracker - API
     */

    namespace Gac\GoodFoodTracker\Core\Exceptions\Validation;

    use Exception;
    use JetBrains\PhpStorm\Pure;

    class FieldsDoNotMatchException extends Exception
    {
        private string $field;
        private string $compareField;

        /**
         * FieldsDoNotMatchException constructor.
         *
         * @param string $field
         * @param string $compareField
         */
        #[Pure] public function __construct(string $field, string $compareField)
        {
            $this->field = $field;
            $this->compareField = $compareField;

            parent::__construct("Values for $field and $compareField don't match", 400);
        }

        /**
         * @return string
         */
        public function getField(): string
        {
            return $this->field;
        }

        /**
         * @return string
         */
        public function getCompareField(): string
        {
            return $this->compareField;
        }
    }
