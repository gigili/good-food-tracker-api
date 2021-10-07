<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-01
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Modules\Restaurant\Controllers;

	use Gac\GoodFoodTracker\Core\Controllers\BaseController;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\FieldsDoNotMatchException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidEmailException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidNumericValueException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MaximumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MinimumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Core\Utility\Validation;
	use Gac\GoodFoodTracker\Core\Utility\ValidationRules;
	use Gac\GoodFoodTracker\Modules\Restaurant\Exceptions\RestaurantNotFoundExceptions;
	use Gac\GoodFoodTracker\Modules\Restaurant\Models\RestaurantModel;
	use Gac\Routing\Request;
	use ReflectionException;

	class RestaurantController extends BaseController
	{
		/**
		 * Endpoint used for filtering a list of restaurants
		 *
		 * @param Request $request
		 *
		 * @OA\Get  (
		 *     path="/restaurant",
		 *     summary="Fetch a list of restaurants",
		 *     description="Endpoint used for getting a list of restaurants",
		 *     tags={"Restaurant"},
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="search",
		 *            description="Value used to filter the result",
		 *            required=false,
		 *     		  @OA\Schema (
		 *                type="string",
		 *                additionalProperties=false
		 *             ),
		 *     ),
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="start",
		 *            description="Pagination start offset",
		 *            required=false,
		 *     		  @OA\Schema (
		 *                type="integer",
		 *                additionalProperties=false
		 *             ),
		 *     ),
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="limit",
		 *            description="Pagination end offset",
		 *            required=false,
		 *     		  @OA\Schema (
		 *                type="integer",
		 *                additionalProperties=false
		 *             ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *                type="object",
		 *                properties={
		 *     			  @OA\Property(
		 *                property="data",
		 *                type="array",
		 *                  @OA\Items(
		 *                        ref="#/components/schemas/RestaurantEntity"
		 *                   )
		 *                 ),
		 *            },
		 *       )
		 *     )
		 * )
		 *
		 */
		public function get_all(Request $request) {
			$search = $request->get("search");
			$start = $request->get("start") ?? 0;
			$limit = $request->get("limit") ?? 10;

			$restaurants = RestaurantModel::filter($search, $start, $limit);

			$request->send([ "data" => $restaurants ]);
		}

		/**
		 * Endpoint used for getting information about a single restaurant
		 *
		 * @throws RestaurantNotFoundExceptions
		 * @throws InvalidUUIDException
		 *
		 * @OA\Get  (
		 *     path="/restaurant/{restaurantID}",
		 *     summary="Fetch a single restaurant",
		 *     description="Endpoint used for getting a single restaurant information",
		 *     tags={"Restaurant"},
		 *     @OA\Parameter(
		 *            in="path",
		 *            name="restaurantID",
		 *            description="ID of a restaurant to fetch the information for",
		 *            required=true,
		 *     		  @OA\Schema (
		 *                type="string",
		 *                additionalProperties=false
		 *             ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *                type="object",
		 *                properties={
		 *     			  @OA\Property(
		 *                property="data",
		 *                type="array",
		 *                  @OA\Items(
		 *                        ref="#/components/schemas/RestaurantEntity"
		 *                   )
		 *                 ),
		 *            },
		 *       )
		 *     )
		 * )
		 */
		public function get(Request $request, string $restaurantID) {
			$restaurant = RestaurantModel::get($restaurantID);
			$request->send([ "data" => $restaurant ]);
		}

		/**
		 * Endpoint used for creating or updating a restaurant
		 *
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 * @throws ReflectionException
		 * @throws RestaurantNotFoundExceptions
		 *
		 * @OA\Post (
		 *     path="/restaurant",
		 *     summary="Create a restaurant",
		 *     description="Endpoint used for creating a restaurant",
		 *     tags={"Restaurant"},
		 *     security={{"bearer": {}}},
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="city_id", type="string"),
		 *     				@OA\Property(property="name", type="string"),
		 *     				@OA\Property(property="adress", type="string"),
		 *     				@OA\Property(property="phone", type="string"),
		 *     				@OA\Property(property="email", type="string"),
		 *     				@OA\Property(property="delivery", type="number"),
		 *     				@OA\Property(property="takeout", type="number"),
		 *     				@OA\Property(property="geo_lat", type="number"),
		 *     				@OA\Property(property="geo_long", type="number"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *		@OA\Response(
		 *        response="201",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/RestaurantEntity"),
		 *           }
		 *       )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="401",
		 *        description="Invalid or missing token",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 *
		 * @OA\Patch  (
		 *     path="/restaurant/{restaurantID}",
		 *     summary="Update a restaurant",
		 *     description="Endpoint used for updating a restaurant",
		 *     tags={"Restaurant"},
		 *     security={{"bearer": {}}},
		 *     @OA\Parameter(
		 *        in="path",
		 *        name="restaurantID",
		 *        description="ID of a restaurant to update the information for",
		 *        required=true
		 *     ),
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="city_id", type="string"),
		 *     				@OA\Property(property="name", type="string"),
		 *     				@OA\Property(property="adress", type="string", nullable=true),
		 *     				@OA\Property(property="phone", type="string", nullable=true),
		 *     				@OA\Property(property="email", type="string", nullable=true),
		 *     				@OA\Property(property="delivery", type="number", nullable=true),
		 *     				@OA\Property(property="takeout", type="number", nullable=true),
		 *     				@OA\Property(property="geo_lat", type="number", nullable=true),
		 *     				@OA\Property(property="geo_long", type="number", nullable=true),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/RestaurantEntity"),
		 *           }
		 *       )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="401",
		 *        description="Invalid or missing token",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 *
		 */
		public function create_or_update(Request $request, ?string $restaurantID) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
			], $request);

			$restaurant = RestaurantModel::create_or_update($request, $restaurantID);
			$request->status(is_null($restaurantID) ? 201 : 200)->send([ "data" => $restaurant ]);
		}

		/**
		 * Endpoint used for deleting a restaurant
		 *
		 * @throws RestaurantNotFoundExceptions
		 * @throws InvalidUUIDException
		 *
		 * @OA\Delete   (
		 *     path="/restaurant/{restaurantID}",
		 *     summary="Delete a restaurant",
		 *     description="Endpoint used for deleting a restaurant",
		 *     tags={"Restaurant"},
		 *     security={{"bearer": {}}},
		 *     @OA\Parameter(
		 *        in="path",
		 *        name="restaurantID",
		 *        description="ID of a restaurant to delete from the database",
		 *        required=true,
		 *     	  @OA\Schema (
		 *           type="string",
		 *           additionalProperties=false
		 *       ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/RestaurantEntity"),
		 *           }
		 *       )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Invalid UUID provided for restaurant ID",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="401",
		 *        description="Invalid or missing token",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="Restaurat not found",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function delete(Request $request, string $restaurantID) {
			$restaurant = RestaurantModel::delete($restaurantID);
			$request->send([ "data" => $restaurant ]);
		}
	}
