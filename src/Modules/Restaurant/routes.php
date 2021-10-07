<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-01
	 * Project: good-food-tracker-api
	 */

	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\Restaurant\Controllers\RestaurantController;

	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes->prefix("/restaurant")
		   ->middleware([ "decode_token" ])
		   ->get("/", [ RestaurantController::class, "get_all" ])
		   ->get("/{string:restaurantID}", [ RestaurantController::class, "get" ])
		   ->post("/", [ RestaurantController::class, "create_or_update" ])
		   ->patch("/{string:restaurantID}", [ RestaurantController::class, "create_or_update" ])
		   ->delete('/{string:restaurantID}', [ RestaurantController::class, 'delete' ])
		   ->save();
