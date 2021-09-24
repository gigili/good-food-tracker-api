<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-24
	 * Project: Good Food Tracker - API
	 */

	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\City\Controllers\CityController;
	use Gac\Routing\Routes;

	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes->prefix("/city")
		   ->route("/", [ CityController::class, "filter_cities" ], [ Routes::GET ])
		   ->add("/{string:cityID}", [ CityController::class, "get_city" ], [ Routes::GET ]);

	$routes->prefix("/city")
		   ->middleware([ "decode_token" ])
		   ->route("/", [ CityController::class, "add_city" ], [ Routes::POST ])
		   ->route("/{string:cityID}", [ CityController::class, "update_city" ], [ Routes::PATCH ])
		   ->add("/{string:cityID}", [ CityController::class, "delete_city" ], [ Routes::DELETE ]);