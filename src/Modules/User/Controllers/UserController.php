<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\User\Controllers;


	use Gac\GoodFoodTracker\Core\Exceptions\InvalidFileTypeException;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidTokenException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileNotFoundException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileNotSavedException;
	use Gac\GoodFoodTracker\Core\Exceptions\UploadFileTooLargeException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\FieldsDoNotMatchException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidEmailException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidNumericValueException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MaximumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MinimumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Core\Utility\FileHandler;
	use Gac\GoodFoodTracker\Core\Utility\Validation;
	use Gac\GoodFoodTracker\Core\Utility\ValidationRules;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailNotSentException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use Gac\GoodFoodTracker\Modules\User\Exceptions\InvalidSearchTermException;
	use Gac\GoodFoodTracker\Modules\User\Models\UserModel;
	use Gac\Routing\Request;
	use ReflectionException;

	class UserController
	{
		/**
		 * Endpoint used for filtering users
		 *
		 * @throws InvalidSearchTermException
		 *
		 * @OA\Get (
		 *     path="/user",
		 *     summary="Filter users endpoint",
		 *     description="Endpoint used to filters users based on the value of search parameter",
		 *     tags={"User"},
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="search", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\RequestBody(
		 *         description="Optional pagination parameters",
		 *         required=false,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="start", type="int"),
		 *     				@OA\Property(property="limit", type="int")
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Successfull result",
		 *			@OA\JsonContent(
		 *     			@OA\Property (
		 *                    property="data",
		 *                    type="array",
		 *     				@OA\Items(ref="#/components/schemas/UserEntity")
		 *                )
		 *            )
		 *     ),
		 *
		 * )
		 *
		 */
		public function get_users(Request $request) {
			$search = $request->get("search");
			$start = $request->get("start") ?? 0;
			$limit = $request->get("limit") ?? 10;
			$result = UserModel::get_users($search, $start, $limit);

			$request->send([
				"data" => $result,
			]);
		}

		/**
		 * Endpoint used for fetch information about a specific user
		 *
		 * @throws InvalidUUIDException
		 * @throws UserNotFoundException
		 *
		 * @OA\Get (
		 *     path="/user/{userID}",
		 *     summary="Fetch information about a specific user",
		 *     description="Endpoint used for fetch information about a specific user",
		 *     tags={"User"},
		 * 	   @OA\Parameter(
		 *         name="userID",
		 *         description="",
		 *         in="path",
		 *         required=true,
		 *         @OA\Schema(
		 *             type="string"
		 *         )
		 *     ),
		 *     @OA\Response(
		 *        response="200",
		 *        description="Successfull result",
		 *			@OA\JsonContent(
		 *                properties={
		 *					@OA\Property (property="data", ref="#/components/schemas/UserEntity"),
		 *                }
		 *            )
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="400",
		 *        description="Invalid UUID provided for userID",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="404",
		 *        description="User not found",
		 *		@OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 * )
		 */
		public function get_user(Request $request, string $userID) {
			$result = UserModel::get_user($userID);

			if ( is_null($result) ) throw new UserNotFoundException();

			$request->send([
				"data" => $result,
			]);
		}

		/**
		 * Endpoint used for updating user account information
		 *
		 * @param Request $request
		 *
		 * @throws FieldsDoNotMatchException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws InvalidUUIDException
		 * @throws MaximumLengthException
		 * @throws MinimumLengthException
		 * @throws RequiredFieldException
		 * @throws InvalidFileTypeException
		 * @throws UploadFileNotSavedException
		 * @throws UploadFileTooLargeException
		 * @throws UserNotFoundException
		 * @throws ReflectionException
		 * @throws UploadFileNotFoundException
		 *
		 * @OA\Put (
		 *     path="/user",
		 *     summary="Update information about a specific user",
		 *     description="Endpoint used for updating information about a specific user",
		 *     tags={"User"},
		 *     security={{"bearer": {}}},
		 *     @OA\Response(
		 *        response="200",
		 *        description="Successfull result",
		 *			@OA\JsonContent(
		 *                properties={
		 *					@OA\Property (property="data", ref="#/components/schemas/UserEntity"),
		 *                }
		 *            )
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="400",
		 *        description="Failed parameter validation",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="404",
		 *        description="User not found",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="406",
		 *        description="Invalid file type",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="413",
		 *        description="Uploaded file was too large",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="500",
		 *        description="Fail upload failed or any other non specific error",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 * )
		 */
		public function update_user_account(Request $request) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
				"email" => [ ValidationRules::REQUIRED, ValidationRules::VALID_EMAIL ],
			], $request);

			$profileImage = isset($_FILES["image"]) ? FileHandler::upload(
				$_FILES["image"],
				BASE_PATH . "/uploads/",
				FileHandler::ALLOWED_TYPES_IMAGES
			) : NULL;

			$user = UserModel::update_user($request, $profileImage);

			$request->send([
				"data" => $user,
			]);
		}

		/**
		 * Endpoint used for deleting a user account
		 *
		 * @throws InvalidTokenException
		 * @throws UserNotFoundException
		 * @throws EmailNotSentException
		 *
		 * @OA\Delete  (
		 *     path="/user",
		 *     summary="Delete account",
		 *     description="Endpoint used for deleting a user account",
		 *     tags={"User"},
		 *     security={{"bearer": {}}},
		 *     @OA\Response(
		 *        response="200",
		 *        description="Successfull result",
		 *			@OA\JsonContent(
		 *                properties={
		 *					@OA\Property (property="data", ref="#/components/schemas/response_with_message_only"),
		 *                }
		 *            )
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="498",
		 *        description="Invalid token provided",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="404",
		 *        description="User not found",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     ),
		 *
		 *     @OA\Response(
		 *        response="500",
		 *        description="Email not sent or any other excpetion",
		 *		  @OA\JsonContent( ref="#/components/schemas/error_response")
		 *     )
		 * )
		 */
		public function delete_user_account(Request $request) {
			UserModel::delete_account();
			$request->send([ "message" => "Account deleted successfully" ]);
		}
	}