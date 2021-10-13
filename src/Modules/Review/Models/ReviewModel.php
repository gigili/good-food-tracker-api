<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Modules\Review\Models;

	use Gac\GoodFoodTracker\Core\Exceptions\Validation\FieldsDoNotMatchException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidEmailException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidNumericValueException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MaximumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\MinimumLengthException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Core\Utility\Validation;
	use Gac\GoodFoodTracker\Core\Utility\ValidationRules;
	use Gac\GoodFoodTracker\Entity\ReviewEntity;
	use Gac\GoodFoodTracker\Modules\Review\Exceptions\ReviewNotFoundException;
	use Gac\Routing\Request;
	use Ramsey\Uuid\Rfc4122\UuidV4;

	class ReviewModel
	{
		public static function filter(?string $search = "", int $start = 0, int $limit = 10) : array {
			$reviewEntity = new ReviewEntity();

			return $reviewEntity->filter(
				filters : [ "comment" => $search, "restaurant_name" => $search, "user_name" => $search ],
				useOr : true,
				start : $start,
				limit : $limit,
				useLike : true
			);
		}

		/**
		 * @param string $reviewID
		 *
		 * @throws InvalidUUIDException
		 * @throws ReviewNotFoundException
		 *
		 * @return ReviewEntity
		 */
		public static function get(string $reviewID) : ReviewEntity {
			if ( !UuidV4::isValid($reviewID) ) throw new InvalidUUIDException();

			$reviewEntity = new ReviewEntity();
			$review = $reviewEntity->get($reviewID);

			if ( ( $review instanceof ReviewEntity ) == false || !isset($review->id) ) throw new ReviewNotFoundException();

			return $review;
		}

		/**
		 * @throws MaximumLengthException
		 * @throws InvalidUUIDException
		 * @throws RequiredFieldException
		 * @throws InvalidEmailException
		 * @throws InvalidNumericValueException
		 * @throws MinimumLengthException
		 * @throws FieldsDoNotMatchException
		 */
		public static function create(Request $request) : ReviewEntity {
			Validation::validate([
				[ 'restaurantID' => [ ValidationRules::REQUIRED, ValidationRules::VALID_UUID ] ],
				[ 'ratingID' => [ ValidationRules::REQUIRED, ValidationRules::NUMERIC ] ],
				[ 'name' => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ] ],
			], $request);

			$restaurantID = $request->get("restaurantID");
			$userID = $_SESSION["userID"];
			$ratingID = $request->get("ratingID");
			$name = $request->get("name");
			$price = floatval($request->get("price") ?? "0");
			$comment = $request->get("comment");
			$delivery = $request->get("delivery");
			$deliveryPrice = floatval($request->get("deliveryPrice") ?? "0");
			$deliveryTime = $request->get("deliveryTime");
		}
	}