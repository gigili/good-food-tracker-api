<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */
	declare( strict_types=1 );

	namespace Gac\GoodFoodTracker\Modules\Auth\Controllers;

	use Exception;
	use Gac\GoodFoodTracker\Core\Utility\Validation;
	use Gac\GoodFoodTracker\Core\Utility\ValidationRules;
	use Gac\GoodFoodTracker\Modules\Auth\Models\AuthModel;
	use Gac\Routing\Request;
	use ReflectionClass;

	class AuthController
	{
		/**
		 * Login endpoint
		 *
		 * @param Request $request
		 *
		 * @OA\Post (
		 *     path="/auth/login",
		 *     summary="Login endpoint",
		 *     description="Endpoint used to authenticate user and obtain JWT tokens",
		 *     tags={"Auth"},
		 *     @OA\RequestBody(
		 *         description="Login parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="username", type="string"),
		 *     				@OA\Property(property="password", type="string")
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Successfull login",
		 *			@OA\JsonContent(ref="#/components/schemas/successful_login"),
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required fileds in the request body",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="User not found (invalid username/password)",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="423",
		 *        description="Account not active",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     )
		 * )
		 *
		 * @OA\Schema (
		 *  schema="successful_login",
		 *     type="object",
		 *     properties={
		 *     @OA\Property(property="user", ref="#/components/schemas/UserEntity"),
		 *     @OA\Property(property="tokens", ref="#/components/schemas/successful_login.tokens")
		 *    }
		 * )
		 *
		 *
		 * @OA\Schema (
		 *    schema="successful_login.tokens",
		 *    properties ={
		 *     @OA\Property(property="access_token", type="string"),
		 *     @OA\Property(property="refresh_token", type="string")
		 *    }
		 * )
		 */
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
				$request->status((int) $ex->getCode() ?? 500)->send([
					'error' => [
						'class' => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? 'Registration failed',
						'field' => ( method_exists($ex, 'getField') ) ? $ex->getField() : '',
					],
				]);
			}
		}

		/**
		 * Register endpoint
		 * @param Request $request
		 *
		 * @OA\Post (
		 *     path="/auth/register",
		 *     summary="Register endpoint",
		 *     description="Endpoint used for registering new accounts",
		 *     tags={"Auth"},
		 *     @OA\RequestBody(
		 *         description="Register parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="name", type="string"),
		 *     				@OA\Property(property="email", type="string"),
		 *     				@OA\Property(property="username", type="string"),
		 *     				@OA\Property(property="password", type="string"),
		 *     				@OA\Property(property="password_again", type="string")
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\Response(
		 *        response="201",
		 *        description="Registration successful",
		 *			@OA\JsonContent(ref="#/components/schemas/successful_registration"),
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required fileds in the request body",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="409",
		 *        description="Username or Email taken",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="500",
		 *        description="Registration failed",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     )
		 * )
		 *
		 * @OA\Schema (
		 *  schema="successful_registration",
		 *     type="object",
		 *     properties={
		 *     @OA\Property(property="message", type="string"),
		 *     @OA\Property(property="user", ref="#/components/schemas/UserEntity"),
		 *    }
		 * )
		 */
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
				$request->status((int) $ex->getCode() ?? 500)->send([
					'error' => [
						"class" => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? "Registration failed",
						"field" => ( method_exists($ex, "getField") ) ? $ex->getField() : "",
					],
				]);
			}
		}

		/**
		 * Verify account endpoint
		 *
		 * @param Request $request
		 *
		 * @OA\Post (
		 *     path="/auth/verify",
		 *     summary="Verify account endpoint",
		 *     description="Endpoint used for verifing user accounts after registration",
		 *     tags={"Auth"},
		 *     @OA\RequestBody(
		 *         description="Parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="activationKey", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Account verified successfully",
		 *			@OA\JsonContent(ref="#/components/schemas/successful_verification"),
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required fileds in the request body",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="412",
		 *        description="Invalid activation key provided",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     )
		 * )
		 *
		 * @OA\Schema(
		 *     schema="successful_verification",
		 *     type="object",
		 *     properties={
		 *     	@OA\Property(property="message", type="string"),
		 *    }
		 * )
		 */
		public function verify_account(Request $request) {
			try {
				Validation::validate([
					"activationKey" => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 10 ] ],
				], $request);

				$activationKey = $request->get("activationKey");

				AuthModel::verify_account($activationKey);

				$request->send([
					"message" => "Account verified successfully",
				]);
			} catch ( Exception $ex ) {
				$request->status((int) $ex->getCode() ?? 500)->send([
					'error' => [
						'class' => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? 'Registration failed',
						'field' => ( method_exists($ex, 'getField') ) ? $ex->getField() : '',
					],
				]);
			}
		}
	}