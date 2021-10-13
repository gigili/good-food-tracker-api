<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	class ReviewImageEntity extends Entity
	{
		public string  $id;
		public string  $review_id;
		public string  $user_id;
		public string  $image;
		public ?string $comment = NULL;

		/** @GAC\Relationship(table='users.user', foreign_key='user_id', references='id', column='name') */
		public ?string   $user_name = NULL;
		protected string $created_at;

		protected array $ignoredColumns = [ "user_name" ];

		public function __construct() {
			$this->created_at = date("c");
			parent::__construct("reviews.review_image");
		}
	}