<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-22
	 * Project: Good Food Tracker - API
	 */

	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\Country\Controllers\CountryController;
	use Gac\Routing\Routes;

	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes->prefix("/country")
		   ->route("/", [ CountryController::class, "filter_countries" ], [ Routes::GET ])
		   ->add("/{string:countryID}", [ CountryController::class, "get_country" ], [ Routes::GET ]);

	$routes->prefix("/country")
		   ->middleware([ "decode_token" ])
		   ->route("/", [ CountryController::class, "add_country" ], [ Routes::POST ])
		   ->route("/{string:countryID}", [ CountryController::class, "update_country" ], [ Routes::PATCH ])
		   ->add("/{string:countryID}", [ CountryController::class, "delete_country" ], [ Routes::DELETE ]);
