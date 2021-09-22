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
		 */
		public function get_country(Request $request, string $countryID) {
			$country = CountryModel::get_country($countryID);
			$request->send([
				"data" => $country,
			]);
		}

		/**
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 * @throws ReflectionException
		 */
		public function add_country(Request $request) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
			], $request);

			$name = $request->get("name");
			$code = $request->get("code");

			$country = CountryModel::add_country($name, $code);

			$request->status(201)->send([
				"data" => $country,
			]);
		}

		/**
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws ReflectionException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 * @throws CountryNotFoundException
		 */
		public function update_country(Request $request, string $countryID) {
			Validation::validate([
				'name' => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
			], $request);

			$name = $request->get('name');
			$code = $request->get('code');

			$country = CountryModel::update_country($countryID, $name, $code);

			$request->send([
				'data' => $country,
			]);
		}

		public function delete_country(Request $request, string $countryID) {

		}
	}