<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	class ReviewEntity extends Entity
	{
		public string  $id;
		public string  $user_id;
		public string  $restaurant_id;
		public int     $rating_id;
		public string  $name;
		public ?float  $price          = 0;
		public ?string $comment        = "";
		public bool    $delivery       = false;
		public ?float  $delivery_price = 0;
		public ?int    $delivery_time  = 0;
		public bool    $takeout        = false;
		public bool    $private        = true;
		public string  $order_date;
		public array   $images         = [];

		/**
		 * @GAC\Relationship(table="users.user" foreign_key="user_id" refrences="id" column="name");
		 */
		public ?string $user_name = NULL;

		/**
		 * @GAC\Relationship(table="places.restaurant" foreign_key="restaurant_id" refrences="id" column="name");
		 */
		public ?string $restaurant_name = NULL;

		/**
		 * @GAC\Relationship(table="reviews.rating" foreign_key="rating_id" refrences="id" column="name");
		 */
		public ?string $rating_name = NULL;

		protected string $created_at;

		protected array $ignoredColumns = [ "user_name", "restaurant_name", "rating_name", "images" ];

		public function __construct() {
			$this->created_at = date("c");
			parent::__construct("reviews.review");
		}
	}