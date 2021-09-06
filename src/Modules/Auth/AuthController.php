<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;

	use Exception;
	use Gac\GoodFoodTracker\Core\Utility\Validation;
	use Gac\GoodFoodTracker\Core\Utility\ValidationRules;
	use Gac\Routing\Request;
	use ReflectionClass;

	class AuthController
	{
		public function login(Request $request) {
			try {
				Validation::validate([
					"username" => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 70 ], ],
					"password" => [ ValidationRules::REQUIRED ],
				], $request);

				$username = $request->get("username");
				$password = $request->get("password");

				$result = AuthModel::login($username, $password);

				$request->send($result);
			} catch ( Exception $ex ) {
				$request->status($ex->getCode() ?? 500)->send([
					'error' => [
						'class' => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? 'Registration failed',
						'field' => ( method_exists($ex, 'getField') ) ? $ex->getField() : '',
					],
				]);
			}
		}

		public function register(Request $request) {
			$name = $request->get("name");
			$email = $request->get("email");
			$username = $request->get('username');
			$password = $request->get('password');

			try {
				Validation::validate([
					"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 200 ] ],
					'email' => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 200 ], ValidationRules::VALID_EMAIL ],
					'username' => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 200 ] ],
					'password' => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 10 ], [ ValidationRules::SAME_AS => "password_again" ] ],
				], $request);

				$newUser = AuthModel::register($name, $email, $username, $password);
				$request->status(201)->send([ "message" => "registration successful", "data" => $newUser ]);
			} catch ( Exception $ex ) {
				$request->status($ex->getCode() ?? 500)->send([
					'error' => [
						"class" => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? "Registration failed",
						"field" => ( method_exists($ex, "getField") ) ? $ex->getField() : "",
					],
				]);
			}
		}
	}