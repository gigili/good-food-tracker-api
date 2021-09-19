<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\Auth\Controllers\AuthController;
	use Gac\Routing\Routes;


	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes->prefix("/auth")
		   ->route("/login", [ AuthController::class, "login" ], [ Routes::POST ])
		   ->route("/register", [ AuthController::class, "register" ], [ Routes::POST ])
		   ->route("/verify", [ AuthController::class, "verify_account" ], [ Routes::POST ])
		   ->route("/request-password-reset", [ AuthController::class, "request_password_reset" ], [ Routes::POST ])
		   ->add("/reset-password", [ AuthController::class, "reset_password" ], [ Routes::POST ]);

	$routes->prefix('/auth')
		   ->middleware([ 'decode_token' ])
		   ->route("/logout", [ AuthController::class, "logout" ], [ Routes::POST ])
		   ->add('/refresh', [ AuthController::class, 'refresh_token' ], [ Routes::POST ]);
