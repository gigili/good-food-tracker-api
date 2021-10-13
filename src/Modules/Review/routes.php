<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\Review\Controllers\ReviewController;

	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes
		->prefix("/review")
		->middleware([ "decode_token" ])
		->get("/", [ ReviewController::class, "filter" ])
		->get("/{string:reviewID}", [ ReviewController::class, "get" ])
		->post("/", [ ReviewController::class, "create" ])
		->patch("/{string:reviewID}", [ ReviewController::class, "update" ])
		->delete("/", [ ReviewController::class, "delete" ])
		->save();