<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-01
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Modules\Restaurant\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class RestaurantNotFoundExceptions extends Exception
	{
		#[Pure] public function __construct() {
			parent::__construct("Restaurant not found", 404);
		}
	}