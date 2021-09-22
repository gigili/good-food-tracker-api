<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-22
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entity;


	use Gac\GoodFoodTracker\Core\Entities\Entity;

	class CountryEntity extends Entity
	{
		public string    $id;
		public string    $name;
		public ?string   $code = NULL;
		protected string $created_at;

		/**
		 * CountryEntity constructor.
		 *
		 * @param string $name
		 */
		public function __construct(?string $name = NULL) {
			$this->created_at = date("c");
			parent::__construct("locations.country");
			if ( !is_null($name) ) {
				$this->name = $name;
			}
		}
	}