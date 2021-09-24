<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-24
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	class CityEntity extends Entity
	{
		public string    $id;
		public string    $country_id;
		public string    $name;
		public ?string   $country_name   = NULL;
		protected string $created_at;
		protected array  $ignoredColumns = [ "country_name" ];

		public function __construct(?string $country_id = NULL, ?string $name = NULL) {
			$this->created_at = date("c");
			parent::__construct("locations.city");

			if ( !is_null($country_id) ) {
				$this->country_id = $country_id;
			}

			if ( !is_null($name) ) {
				$this->name = $name;
			}
		}
	}