<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	use Gac\GoodFoodTracker\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Modules\Auth\AuthController;


	if ( !isset($routes) ) {
		throw new AppNotInitializedException();
	}

	$routes->prefix("/Auth")
		   ->route("/login", [ AuthController::class, "login" ])
		   ->route("/register", [ AuthController::class, "register" ])
		   ->route("/verify", [ AuthController::class, "verify_account" ])
		   ->route("/request-password-reset", [ AuthController::class, "request_password_reset" ])
		   ->add("/reset-password", [ AuthController::class, "reset_password" ]);