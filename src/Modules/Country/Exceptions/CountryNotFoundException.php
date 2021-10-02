<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-22
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Country\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class CountryNotFoundException extends Exception
	{
		/**
		 * CountryNotFoundException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Country not found", 404);
		}
	}
