<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;

	use Gac\Routing\Request;

	class AuthController
	{
		public function login(Request $request) {
			$request->send([ 'message' => 'login endpoint' ]);
		}
	}