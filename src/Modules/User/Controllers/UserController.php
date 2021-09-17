<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\User\Controllers;


	use Gac\GoodFoodTracker\Core\Exceptions\InvalidFileTypeException;
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
		 *			@OA\JsonContent(ref="#/components/schemas/search_users_success_result"),
		 *     ),
		 *
		 * )
		 *
		 *
		 * @OA\Schema (
		 *  schema="search_users_success_result",
		 *     type="array",
		 *     properties={
		 *     	@OA\Property(
		 *       property="data",
		 *     ),
		 *    },
		 *   @OA\Items(
		 *     ref="#/components/schemas/UserEntity"
		 *  )
		 * )
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
		 * @throws InvalidUUIDException
		 */
		public function get_user(Request $request, string $userID) {
			$result = UserModel::get_user($userID);
			$request->send([
				"data" => $result,
			]);
		}

		/**
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
		 */
		public function update_user_account(Request $request) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
				"email" => [ ValidationRules::REQUIRED, ValidationRules::VALID_EMAIL ],
			], $request);

			$profileImage = isset($_FILES["image"]) ? FileHandler::upload(
				$_FILES["image"],
				"./src/uploads/",
				FileHandler::ALLOWED_TYPES_IMAGES
			) : NULL;

			$user = UserModel::update_user($request, $profileImage);

			$request->send([
				"data" => $user,
			]);
		}

		public function delete_user_account(Request $request) {

		}
	}