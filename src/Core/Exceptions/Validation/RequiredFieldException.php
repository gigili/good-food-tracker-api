<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions\Validation;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class RequiredFieldException extends Exception
	{
		private string $field = "";

		#[Pure] public function __construct($field = "") {
			$this->field = $field;
			parent::__construct("Missing value for required field $field", 400);
		}

		/**
		 * @return String
		 */
		public function getField() : string {
			return $this->field;
		}

		public function __toString() {
			parent::__toString();
			return $this->field;
		}
	}
