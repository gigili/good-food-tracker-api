<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions\Validation;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class MaximumLengthException extends Exception
	{
		private float  $value = 0;
		private string $field = "";

		/**
		 * MaximumLengthException constructor.
		 *
		 * @param float|int $value
		 * @param string $field
		 */
		#[Pure] public function __construct(float|int $value, string $field) {
			$this->value = $value;
			$this->field = $field;

			parent::__construct("Maximum length of $value exceeded for field $field", 400);
		}

		/**
		 * @return float|int
		 */
		public function getValue() : float|int {
			return $this->value;
		}

		/**
		 * @return string
		 */
		public function getField() : string {
			return $this->field;
		}
	}
