<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */
	declare( strict_types=1 );

	namespace Gac\GoodFoodTracker\Modules\Review\Controllers;

	use Gac\GoodFoodTracker\Core\Controllers\BaseController;
	use Gac\GoodFoodTracker\Core\Exceptions\ForbiddenException;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidInstanceException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\FieldsDoNotMatchException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidEmailException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidNumericValueException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MaximumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MinimumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Modules\Review\Exceptions\ReviewNotFoundException;
	use Gac\GoodFoodTracker\Modules\Review\Models\ReviewModel;
	use Gac\Routing\Request;
	use ReflectionException;

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
		 * @param Request $request
		 * @param string|null $reviewID
		 *
		 * @throws FieldsDoNotMatchException
		 * @throws InvalidEmailException
		 * @throws InvalidInstanceException
		 * @throws InvalidNumericValueException
		 * @throws InvalidUUIDException
		 * @throws MaximumLengthException
		 * @throws MinimumLengthException
		 * @throws ReflectionException
		 * @throws RequiredFieldException
		 * @throws ReviewNotFoundException
		 */
		public function save_review(Request $request, ?string $reviewID = NULL) {
			$review = ReviewModel::save_review($request, $reviewID);
			$request->status(is_null($reviewID) ? 201 : 200)->send([ "review" => $review ]);
		}

		/**
		 * @throws ForbiddenException
		 * @throws InvalidUUIDException
		 * @throws ReviewNotFoundException
		 */
		public function delete(Request $request, string $reviewID) {
			$review = ReviewModel::delete($reviewID);
			$request->send([ "review" => $review ]);
		}
	}