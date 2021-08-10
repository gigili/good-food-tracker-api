<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;

	use Exception;
	use Gac\GoodFoodTracker\exceptions\validation\MaximumLengthException;
	use Gac\GoodFoodTracker\exceptions\validation\RequiredFieldException;
	use Gac\GoodFoodTracker\utilities\Validation;
	use Gac\GoodFoodTracker\utilities\ValidationRules;
	use Gac\Routing\Request;

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

				$request->send([ 'message' => 'login endpoint' ]);
			} catch ( RequiredFieldException $ex ) {
				$request->status(400)->send([
					"error" => [
						"message" => "Missing required value",
						"field" => $ex->getField(),
					],
				]);
			} catch ( MaximumLengthException $ex ) {
				$request->status(400)->send([
					'error' => [
						'message' => "Maximum length of {$ex->getValue()} exceeded",
						'field' => $ex->getField(),
					],
				]);
			} catch ( Exception $ex ) {
				$request->status($ex->getCode() ?? 500)->send([ "error" => [ "message" => $ex->getMessage(), "field" => "" ] ]);
			}
		}

		public function register(Request $request) {
			$name = $request->get("name");
			$email = $request->get("email");
			$username = $request->get('username');
			$password = $request->get('password');
			$password_repeat = $request->get('password_repeat');

			$request->send([ 'message' => 'login endpoint' ]);
		}
	}