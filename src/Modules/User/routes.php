<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\User\Controllers\UserController;
	use Gac\Routing\Routes;

	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes->prefix("/user")
		   ->middleware([ "decode_token" ])
		   ->route("/", [ UserController::class, "get_users" ], [ Routes::GET ])
		   ->route("/", [ UserController::class, "update_user_account" ], [ Routes::PATCH ])
		   ->route("/", [ UserController::class, "delete_user_account" ], [ Routes::DELETE ])
		   ->add("/{string:userID}", [ UserController::class, "get_user" ], [ Routes::GET ]);
