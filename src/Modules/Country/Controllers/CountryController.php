<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-22
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Country\Controllers;


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
	use Gac\GoodFoodTracker\Modules\Country\Exceptions\CountryNotFoundException;
	use Gac\GoodFoodTracker\Modules\Country\Models\CountryModel;
	use Gac\Routing\Request;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use ReflectionException;

	class CountryController extends BaseController
	{
		/**
		 * Endpoint used for filtering countries
		 *
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 *
		 * @OA\Get  (
		 *     path="/country",
		 *     summary="Fetch a list of countries",
		 *     description="Endpoint used for get a list of countries",
		 *     tags={"Country"},
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="search",
		 *            description="Value used to filter the countries",
		 *            required=true
		 *     ),
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="start",
		 *            description="Pagination start offset",
		 *            required=false
		 *     ),
		 *     @OA\Parameter(
		 *            in="query",
		 *            name="limit",
		 *            description="Pagination end offset",
		 *            required=false
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull filtered countries",
		 *			@OA\JsonContent(
		 *                type="object",
		 *                  properties={
		 *     			  @OA\Property(
		 *                property="data",
		 *                    type="array",
		 *                  @OA\Items(
		 *                        ref="#/components/schemas/CountryEntity"
		 *                   )
		 *                 ),
		 *            },
		 *       )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 *
		 */
		public function filter_countries(Request $request) {
			Validation::validate([
				"search" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
			], $request);

			$search = $request->get("search");
			$start = $request->get("start") ?? 0;
			$limit = $request->get("limit") ?? 10;

			$countries = CountryModel::filter($search, $start, $limit);

			$request->send([
				"data" => $countries,
			]);
		}

		/**
		 * Endpoint used for fetching information about single country
		 *
		 * @throws CountryNotFoundException
		 * @throws InvalidUUIDException
		 *
		 * @OA\Get  (
		 *     path="/country/{countryID}",
		 *     summary="Fetch a information about a specific country",
		 *     description="Endpoint used for getting information about a specific country",
		 *     tags={"Country"},
		 *     @OA\Parameter(
		 *            in="path",
		 *            name="countryID",
		 *            description="ID of a country",
		 *            required=true
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property (property="data", ref="#/components/schemas/CountryEntity"),
		 *                }
		 *            )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function get_country(Request $request, string $countryID) {
			$country = CountryModel::get_country($countryID);
			$request->send([
				"data" => $country,
			]);
		}

		/**
		 * Endpoint used for add a new country
		 *
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 * @throws ReflectionException
		 *
		 * @OA\Post   (
		 *     path="/country",
		 *     summary="Add new country",
		 *     description="Endpoint used for adding a new country",
		 *     tags={"Country"},
		 *     security={{"bearer": {}}},
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="name", type="string"),
		 *     				@OA\Property(property="code", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *		@OA\Response(
		 *        response="201",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property (property="data", ref="#/components/schemas/CountryEntity"),
		 *                }
		 *            )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function add_country(Request $request) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
				"code" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 2 ] ],
			], $request);

			$name = $request->get("name");
			$code = $request->get("code");

			$country = CountryModel::add_country($name, $code);

			$request->status(201)->send([
				"data" => $country,
			]);
		}

		/**
		 * Endpoint used for updating country information
		 *
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws ReflectionException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 * @throws CountryNotFoundException
		 *
		 * @OA\Patch    (
		 *     path="/country/{countryID}",
		 *     summary="Update country",
		 *     description="Endpoint used for updateing country information",
		 *     tags={"Country"},
		 *     security={{"bearer": {}}},
		 *     @OA\Parameter (
		 *            in="path",
		 *            required=true,
		 *            name="countryID",
		 *            description="ID of a country being updated",
		 *     ),
		 *     @OA\RequestBody(
		 *         description="Required parameters",
		 *         required=true,
		 *         @OA\MediaType(
		 *            mediaType="application/json",
		 *			  @OA\Schema(
		 *                properties={
		 *     				@OA\Property(property="name", type="string"),
		 *     				@OA\Property(property="code", type="string"),
		 *                },
		 *              ),
		 *            ),
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property (property="data", ref="#/components/schemas/CountryEntity"),
		 *                }
		 *            )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="Country not found",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function update_country(Request $request, string $countryID) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
				"code" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 2 ] ],
			], $request);

			$name = $request->get("name");
			$code = $request->get("code");

			$country = CountryModel::update_country($countryID, $name, $code);

			$request->send([
				"data" => $country,
			]);
		}

		/**
		 * Endpoint for deleting a country
		 *
		 * @throws InvalidUUIDException
		 * @throws CountryNotFoundException
		 *
		 * @OA\Delete (
		 *     path="/country/{countryID}",
		 *     summary="Delete country",
		 *     security={{"bearer": {}}},
		 *     description="Endpoint used for deleting a country",
		 *     tags={"Country"},
		 *     @OA\Parameter (
		 *            in="path",
		 *            required=true,
		 *            name="countryID",
		 *            description="ID of a country being updated",
		 *     ),
		 *		@OA\Response(
		 *        response="200",
		 *        description="Successfull response",
		 *			@OA\JsonContent(
		 *                properties = {
		 *     				@OA\Property (property="data", ref="#/components/schemas/CountryEntity"),
		 *                }
		 *            )
		 *     ),
		 *     @OA\Response(
		 *        response="400",
		 *        description="Missing required arguments",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 *     @OA\Response(
		 *        response="404",
		 *        description="Country not found",
		 *			@OA\JsonContent(ref="#/components/schemas/error_response"),
		 *     ),
		 * )
		 */
		public function delete_country(Request $request, string $countryID) {
			if ( empty($countryID) || !UuidV4::isValid($countryID) ) throw new InvalidUUIDException();

			$country = CountryModel::delete_country($countryID);

			$request->send([ "data" => $country ]);
		}
	}