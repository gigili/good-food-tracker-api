<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-24
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\City\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class CityNotFoundException extends Exception
	{
		#[Pure] public function __construct() {
			parent::__construct("City not found", 404);
		}
	}