<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\exceptions\validation;


	use Exception;
	use JetBrains\PhpStorm\Pure;

	class InvalidUUIDException extends Exception
	{

		/**
		 * InvalidUUIDException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Invalid UUID value provided", 400);
		}
	}