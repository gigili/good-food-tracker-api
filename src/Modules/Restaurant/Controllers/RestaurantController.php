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
		public function get_all(Request $request) {
			$search = $request->get("search");
			$start = $request->get("start") ?? 0;
			$limit = $request->get("limit") ?? 10;

			$restaurants = RestaurantModel::filter($search, $start, $limit);

			$request->send([ "data" => $restaurants ]);
		}

		/**
		 * @throws RestaurantNotFoundExceptions
		 * @throws InvalidUUIDException
		 */
		public function get(Request $request, string $restaurantID) {
			$restaurant = RestaurantModel::get($restaurantID);
			$request->send([ "data" => $restaurant ]);
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
		 * @throws RestaurantNotFoundExceptions
		 */
		public function create_or_update(Request $request, ?string $restaurantID) {
			Validation::validate([
				"name" => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ],
			], $request);

			$restaurant = RestaurantModel::create_or_update($request, $restaurantID);
			$request->status(is_null($restaurantID) ? 201 : 200)->send([ "data" => $restaurant ]);
		}

		/**
		 * @throws RestaurantNotFoundExceptions
		 * @throws InvalidUUIDException
		 */
		public function delete(Request $request, string $restaurantID) {
			$restaurant = RestaurantModel::delete($restaurantID);
			$request->send([ "data" => $restaurant ]);
		}
	}