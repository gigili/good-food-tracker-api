<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;

	use Exception;
	use Gac\GoodFoodTracker\exceptions\validation\FieldsDoNotMatchException;
	use Gac\GoodFoodTracker\exceptions\validation\InvalidEmailException;
	use Gac\GoodFoodTracker\exceptions\validation\MaximumLengthException;
	use Gac\GoodFoodTracker\exceptions\validation\MinimumLengthException;
	use Gac\GoodFoodTracker\exceptions\validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UsernameTakenException;
	use Gac\GoodFoodTracker\Utility\Validation;
	use Gac\GoodFoodTracker\Utility\ValidationRules;
	use Gac\Routing\Request;
	use ReflectionClass;

	class AuthController
	{
		public function login(Request $request) {
			try {
				Validation::validate([
					"username" => [
						ValidationRules::REQUIRED,
						[ ValidationRules::MAX_LENGTH => 70 ],
					],
					"password" => [ ValidationRules::REQUIRED ],
				], $request);

				$username = $request->get("username");
				$password = $request->get("password");

				$user = AuthModel::login($username, $password);

				$request->send([ 'message' => 'login endpoint', "data" => $user ]);
			} catch (
			RequiredFieldException |
			MaximumLengthException |
			Exception $ex
			) {
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

				$user = AuthModel::register($name, $email, $username, $password);
				$request->status(201)->send([ 'message' => 'Registration successful', 'data' => $user ]);
			} catch (
			RequiredFieldException |
			MaximumLengthException |
			MinimumLengthException |
			InvalidEmailException |
			FieldsDoNotMatchException |
			UsernameTakenException |
			EmailTakenException |
			Exception $ex
			) {
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