<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-24
	 * Project: Good Food Tracker - API
	 */
	declare( strict_types=1 );

	namespace Gac\GoodFoodTracker\Modules\City\Controllers;

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
	use Gac\GoodFoodTracker\Modules\City\Exceptions\CityFailedDeletingException;
	use Gac\GoodFoodTracker\Modules\City\Exceptions\CityFailedSavingException;
	use Gac\GoodFoodTracker\Modules\City\Exceptions\CityNotFoundException;
	use Gac\GoodFoodTracker\Modules\City\Models\CityModel;
	use Gac\Routing\Request;
	use ReflectionException;

	class CityController extends BaseController
	{
		/**
		 * Endpoint used for get a list of cities
		 *
		 * @param Request $request
		 *
		 * @OA\Get  (
		 *     path="/city",
		 *     summary="Fetch a list of cities",
		 *     description="Endpoint used for getting a list of cities",
		 *     tags={"City"},
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="search",
		 *            description="Value used to filter the cities",
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
		 *                        ref="#/components/schemas/CityEntity"
		 *                   )
		 *                 ),
		 *            },
		 *       )
		 *     )
		 * )
		 *
		 */
		public function filter_cities(Request $request) {
			$search = $request->get("search");
			$start = (int) $request->get("start") ?? 0;
			$limit = (int) $request->get("limit") ?? 10;

			$cities = CityModel::filter($search, $start, $limit);

			$request->send([
				"data" => $cities,
			]);
		}

		/**
		 * Endpoint for getting information about a city
		 *
		 * @param Request $request Default request object containing all the request data
		 * @param string $cityID UUID of a city to get the information for
		 *
		 * @throws CityNotFoundException
		 * @throws InvalidUUIDException
		 *
		 * @OA\Get  (
		 *     path="/city/{cityID}",
		 *     summary="Fetch a single city",
		 *     description="Endpoint used for get information about a single city",
		 *     tags={"City"},
		 *     @OA\Parameter(
		 *            in="path",
		 *            name="cityID",
		 *            description="ID of a city we are looking for",
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
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/CityEntity"),
		 *           }
		 *       )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="City not found",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 *
		 */
		public function get_city(Request $request, string $cityID) {
			$city = CityModel::get($cityID);

			$request->send([
				"data" => $city,
			]);
		}

		/**
		 * Endpoint used for adding a new city
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
		 * @throws ReflectionException
		 *
		 * @OA\Post (
		 *     path="/city",
		 *     summary="Create a city",
		 *     description="Endpoint used for creating a city",
		 *     tags={"City"},
		 *     security={{"bearer": {}}},
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="name", type="string"),
		 *     				@OA\Property(property="country_id", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *		@OA\Response(
		 *        response="201",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/CityEntity"),
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
		 */
		public function add_city(Request $request) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 2 ] ],
				"countryID" => [ ValidationRules::REQUIRED, ValidationRules::VALID_UUID ],
			], $request);

			$name = $request->get("name");
			$countryID = $request->get("countryID");

			$city = CityModel::add($name, $countryID);

			$request->status(201)->send([
				"data" => $city,
			]);
		}

		/**
		 * Endpoint used for updating city information
		 *
		 * @param Request $request
		 * @param string $cityID
		 *
		 * @throws CityNotFoundException
		 * @throws FieldsDoNotMatchException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws InvalidUUIDException
		 * @throws MaximumLengthException
		 * @throws MinimumLengthException
		 * @throws ReflectionException
		 * @throws RequiredFieldException
		 * @throws CityFailedSavingException
		 *
		 * @OA\Patch (
		 *     path="/city/{cityID}",
		 *     summary="Update city",
		 *     description="Endpoint used for updating city information",
		 *     tags={"City"},
		 *     security={{"bearer": {}}},
		 *     @OA\Parameter (
		 *            in="path",
		 *            required=true,
		 *            name="cityID",
		 *            description="ID of a city being updated",
		 *     		  @OA\Schema (
		 *                type="string",
		 *                additionalProperties=false
		 *             ),
		 *     ),
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="name", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/CityEntity"),
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
		 */
		public function update_city(Request $request, string $cityID) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 2 ] ],
			], $request);

			$name = $request->get("name");

			$city = CityModel::update($cityID, $name);

			$request->send([
				"data" => $city,
			]);
		}

		/**
		 * Endpoint used for deleting a city
		 *
		 * @param Request $request
		 * @param string $cityID
		 *
		 * @throws CityNotFoundException
		 * @throws InvalidUUIDException
		 * @throws CityFailedSavingException
		 * @throws CityFailedDeletingException
		 *
		 * @OA\Delete (
		 *     path="/city/{cityID}",
		 *     summary="Delete city",
		 *     description="Endpoint used for deleting a city",
		 *     tags={"City"},
		 *     security={{"bearer": {}}},
		 *     @OA\Parameter (
		 *            in="path",
		 *            required=true,
		 *            name="cityID",
		 *            description="ID of a city being updated",
		 *     			@OA\Schema (
		 *                type="string",
		 *                additionalProperties=false
		 *             ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *            properties = {
		 *     			@OA\Property (property="data", ref="#/components/schemas/CityEntity"),
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
		 */
		public function delete_city(Request $request, string $cityID) {
			$city = CityModel::delete($cityID);
			$request->send([ "data" => $city ]);
		}
	}
