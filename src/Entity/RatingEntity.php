<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	class RatingEntity extends Entity
	{
		public int    $id;
		public string $name;

		public function __construct() {
			parent::__construct('reviews.rating');
		}
	}