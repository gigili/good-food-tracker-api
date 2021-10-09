<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-09
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	class RoleEntity extends Entity
	{
		public string    $id;
		public string    $name;
		public int       $level;
		protected string $created_at;

		public function __construct() {
			$this->created_at = date("c");
			parent::__construct("auth.role");
		}
	}


