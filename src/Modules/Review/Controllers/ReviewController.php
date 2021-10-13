<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */
	declare( strict_types=1 );

	namespace Gac\GoodFoodTracker\Modules\Review\Controllers;

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
	use Gac\GoodFoodTracker\Modules\Review\Exceptions\ReviewNotFoundException;
	use Gac\GoodFoodTracker\Modules\Review\Models\ReviewModel;
	use Gac\Routing\Request;

	class ReviewController extends BaseController
	{
		public function filter(Request $request) : void {
			$search = $request->get("search");
			$start = $request->get("start") ?? 0;
			$limit = $request->get("limit") ?? 10;

			$reviews = ReviewModel::filter($search, $start, $limit);
			$request->send([ "reviews" => $reviews ]);
		}

		/**
		 * @throws ReviewNotFoundException
		 * @throws InvalidUUIDException
		 */
		public function get(Request $request, string $reviewID) : void {
			$review = ReviewModel::get($reviewID);
			$request->send([ "review" => $review ]);
		}

		/**
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws InvalidEmailException
		 * @throws RequiredFieldException
		 * @throws InvalidNumericValueException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 */
		public function create(Request $request) {
			$review = ReviewModel::create($request);
		}
	}