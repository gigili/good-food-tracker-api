<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\exceptions\validation;


	use Exception;
	use JetBrains\PhpStorm\Pure;

	class RequiredFieldException extends Exception
	{
		private string $field = "";

		#[Pure] public function __construct($field = "") {
			$this->field = $field;
			parent::__construct();
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