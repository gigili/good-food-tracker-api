<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */
	declare( strict_types=1 );

	namespace Gac\GoodFoodTracker\Modules\Auth\Controllers;

	use Exception;
	use Gac\GoodFoodTracker\Core\Controllers\BaseController;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidTokenException;
	use Gac\GoodFoodTracker\Core\Utility\Validation;
	use Gac\GoodFoodTracker\Core\Utility\ValidationRules;
	use Gac\GoodFoodTracker\Modules\Auth\Models\AuthModel;
	use Gac\Routing\Request;
	use ReflectionClass;

	class AuthController extends BaseController
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
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property(property="user", ref="#/components/schemas/UserEntity"),
		 *     				@OA\Property(
		 *                        property="tokens",
		 *                        type="object",
		 *                        properties = {
		 *    						@OA\Property(property="access_token", type="string"),
		 *     						@OA\Property(property="refresh_token", type="string")
		 *                        }
		 *                    )
		 *                }
		 *            )
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
		 *
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
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property(property="message", type="string"),
		 *     				@OA\Property(property="user", ref="#/components/schemas/UserEntity"),
		 *                }
		 *            )
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
		 */
		public function register(Request $request) {
			$name = $request->get("name");
			$email = $request->get("email");
			$username = $request->get('username');
			$password = $request->get('password');

			try {
				Validation::validate([
					"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 200 ] ],
					'email' => [
						ValidationRules::REQUIRED,
						[ ValidationRules::MAX_LENGTH => 200 ],
						ValidationRules::VALID_EMAIL,
					],
					'username' => [ ValidationRules::REQUIRED, [ ValidationRules::MAX_LENGTH => 200 ] ],
					'password' => [
						ValidationRules::REQUIRED,
						[ ValidationRules::MIN_LENGTH => 10 ],
						[ ValidationRules::SAME_AS => "password_again" ],
					],
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
		 *			@OA\JsonContent(ref="#/components/schemas/response_with_message_only"),
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
						'message' => $ex->getMessage() ?? 'Account verification failed',
						'field' => ( method_exists($ex, 'getField') ) ? $ex->getField() : '',
					],
				]);
			}
		}

		/**
		 * Request password reset code
		 *
		 * @param Request $request
		 *
		 * @OA\Post (
		 *     path="/auth/request-password-reset",
		 *     summary="Request password reset code endpoint",
		 *     description="Endpoint used for request a new password reset code",
		 *     tags={"Auth"},
		 *     @OA\RequestBody(
		 *         description="Parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="emailOrUsername", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Password reset code sent to email",
		 *			@OA\JsonContent(ref="#/components/schemas/response_with_message_only"),
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required fileds in the request body",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="User account not found",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="500",
		 *        description="Unable to send reset code via email",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     )
		 * )
		 *
		 */
		public function request_password_reset(Request $request) {
			try {
				Validation::validate([
					"emailOrUsername" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
				], $request);

				$emailOrUsername = $request->get("emailOrUsername");
				AuthModel::generate_password_reset_code($emailOrUsername);

				$request->send([ "message" => "Password reset code sent to the email address linked to the account" ]);
			} catch ( Exception $ex ) {
				$request->status((int) $ex->getCode() ?? 500)->send([
					'error' => [
						'class' => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? 'Failed generating password reset code',
						'field' => ( method_exists($ex, 'getField') ) ? $ex->getField() : '',
					],
				]);
			}
		}

		/**
		 * Reset password endpoint
		 *
		 * @param Request $request
		 *
		 * @OA\Post (
		 *     path="/auth/reset-password",
		 *     summary="Reset password endpoint",
		 *     description="Endpoint used for resetting a new password",
		 *     tags={"Auth"},
		 *     @OA\RequestBody(
		 *         description="Parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="passwordResetCode", type="string"),
		 *     				@OA\Property(property="newPassword", type="string"),
		 *     				@OA\Property(property="newPasswordAgain", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Password reset successfully",
		 *			@OA\JsonContent(ref="#/components/schemas/response_with_message_only"),
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required fileds in the request body",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="User account not found",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="500",
		 *        description="Unable to send password reset confirmation email",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     )
		 * )
		 */
		public function reset_password(Request $request) {
			try {
				Validation::validate([
					"passwordResetCode" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
					"newPassword" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 10 ] ],
					"newPasswordAgain" => [
						ValidationRules::REQUIRED,
						[ ValidationRules::MIN_LENGTH => 10 ],
						[ ValidationRules::SAME_AS => "newPassword" ],
					],
				], $request);

				$passwordResetCode = $request->get("passwordResetCode");
				$newPassword = $request->get("newPassword");

				AuthModel::reset_password($passwordResetCode, $newPassword);

				$request->send([
					"message" => "Password reset successfully",
				]);
			} catch ( Exception $ex ) {
				$request->status((int) $ex->getCode() ?? 500)->send([
					'error' => [
						'class' => ( new ReflectionClass($ex) )->getShortName(),
						'message' => $ex->getMessage() ?? 'Password reset failed',
						'field' => ( method_exists($ex, 'getField') ) ? $ex->getField() : '',
					],
				]);
			}
		}

		/**
		 * Logout endpoint
		 *
		 * @param Request $request
		 *
		 * @throws InvalidTokenException
		 * @OA\Post (
		 *     path="/auth/logout",
		 *     summary="Logout endpoint",
		 *     description="Endpoint used for loggin user out and invalidating tokens",
		 *     tags={"Auth"},
		 *     @OA\Parameter(
		 *            in="header",
		 *            name="Authorization",
		 *            description="Refresh or access token",
		 *            required=true
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Logout successfull",
		 *			@OA\JsonContent(ref="#/components/schemas/response_with_message_only"),
		 *     ),
		 *     @OA\Response(
		 *        response="401",
		 *        description="Missing token",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="498",
		 *        description="Invalid token exception",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="500",
		 *        description="Invalid or inccorent token provided: InvalidArgumentException | UnexpectedValueException | SignatureInvalidException | BeforeValidException | BeforeValidException | ExpiredException",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function logout(Request $request) {
			$userID = $_SESSION["userID"] ?? NULL;
			if ( is_null($userID) ) throw new InvalidTokenException();

			$this->redis->del([ "{refresh_token:$userID}", "{access_token:$userID}" ]);
			$request->send([
				"message" => "OK",
			]);
		}

		/**
		 * Refresh token endpoint
		 *
		 * @param Request $request
		 *
		 * @throws InvalidTokenException
		 *
		 * @OA\Post (
		 *     path="/auth/refresh",
		 *     summary="Refresh token endpoint",
		 *     description="Endpoint used for generating a fresh access token based of refresh token",
		 *     tags={"Auth"},
		 *     @OA\Parameter(
		 *            in="header",
		 *            name="Authorization",
		 *            description="Refresh token",
		 *            required=true
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull generated new access token",
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property (property="accessToken", type="string"),
		 *     				@OA\Property (property="refreshToken", type="string", nullable=true),
		 *                }
		 *            )
		 *     ),
		 *     @OA\Response(
		 *        response="401",
		 *        description="Missing token",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="498",
		 *        description="Invalid token exception",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="500",
		 *        description="Invalid or inccorent token provided: InvalidArgumentException | UnexpectedValueException | SignatureInvalidException | BeforeValidException | BeforeValidException | ExpiredException",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function refresh_token(Request $request) {
			$userID = $_SESSION["userID"];

			if ( is_null($this->redis->get("{refresh_token:$userID}")) ) throw new InvalidTokenException();

			$newToken = generate_token($userID, false, true);
			$request->send($newToken);
		}
	}