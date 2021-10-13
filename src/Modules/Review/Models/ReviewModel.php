<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Modules\Review\Models;

	use Gac\GoodFoodTracker\Core\Exceptions\ForbiddenException;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidInstanceException;
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
	use ReflectionException;

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
		 * @return ReviewEntity
		 */
		public static function save_review(Request $request, ?string $reviewID = NULL) : ReviewEntity {
			Validation::validate([
				[ 'restaurantID' => [ ValidationRules::REQUIRED, ValidationRules::VALID_UUID ] ],
				[ 'ratingID' => [ ValidationRules::REQUIRED, ValidationRules::NUMERIC ] ],
				[ 'name' => [ ValidationRules::REQUIRED, [ ValidationRules::MIN_LENGTH => 3 ] ] ],
			], $request);

			$userID = $_SESSION["userID"] ?? NULL;
			if ( is_null($userID) || !UuidV4::isValid($userID) ) {
				throw new InvalidUUIDException("userID");
			}

			$restaurantID = $request->get("restaurantID");
			$ratingID = $request->get("ratingID");
			$name = $request->get("name");
			$price = floatval($request->get("price") ?? "0");
			$comment = $request->get("comment");
			$delivery = $request->get("delivery");
			$deliveryPrice = floatval($request->get("deliveryPrice") ?? "0");
			$deliveryTime = $request->get("deliveryTime");
			$takeout = $request->get("takeout");
			$orderDate = $request->get("orderDate");
			$private = $request->get("private") ?? true;

			$newReview = new ReviewEntity();

			if ( !is_null($reviewID) ) {
				$newReview = ( new ReviewEntity() )->get($reviewID);
				if ( ( $newReview instanceof ReviewEntity ) || !isset($newReview->id) ) throw new ReviewNotFoundException();
			}

			$newReview->user_id = $userID;
			$newReview->restaurant_id = $restaurantID;
			$newReview->rating_id = $ratingID;
			$newReview->name = $name;
			$newReview->price = $price;
			$newReview->comment = $comment;
			$newReview->delivery = $delivery;
			$newReview->delivery_price = $deliveryPrice;
			$newReview->delivery_time = $deliveryTime;
			$newReview->takeout = $takeout;
			$newReview->private = $private;
			$newReview->order_date = date("Y-m-d H:i:s", strtotime($orderDate));

			$review = $newReview->save();

			//TODO: Handle uploading and storing of review images and their comments

			if ( ( $review instanceof ReviewEntity ) == false || !isset($review->id) ) throw new InvalidInstanceException(ReviewEntity::class);

			return $review;
		}

		/**
		 * @param string $reviewID
		 *
		 * @throws ForbiddenException
		 * @throws InvalidUUIDException
		 * @throws ReviewNotFoundException
		 *
		 * @return ReviewEntity
		 */
		public static function delete(string $reviewID) : ReviewEntity {
			$userID = $_SESSION['userID'] ?? NULL;
			if ( is_null($userID) || !UuidV4::isValid($userID) ) {
				throw new InvalidUUIDException('userID');
			}

			if ( !UuidV4::isValid($reviewID) ) {
				throw new InvalidUUIDException('reviewID');
			}

			$reviewEntity = new ReviewEntity();
			$review = $reviewEntity->get($reviewID);

			if ( !( $review instanceof ReviewEntity ) || !isset($review->id) ) {
				throw new ReviewNotFoundException();
			}

			if ( $review->user_id !== $userID ) {
				throw new ForbiddenException();
			}

			//TODO: Delete all the images linked to this review so we don't waste disk space Issue #218

			return $review->delete();
		}
	}