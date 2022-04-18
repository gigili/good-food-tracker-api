<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-24
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	/**
	 * CityEntity Class
	 *
	 * @OA\Schema (
	 *  schema="CityEntity",
	 *  type="object",
	 *  additionalProperties=false,
	 *  properties={
	 *  	@OA\Property(property="id", type="string"),
	 *  	@OA\Property(property="name", type="string"),
	 *  	@OA\Property(property="country_id", type="string"),
	 *  	@OA\Property(property="country_name", type="string", nullable=true),
	 *  }
	 * )
	 */
	class CityEntity extends Entity
	{
		/**
		 * @var string
		 */
		public string $id;

		/**
		 * @var string
		 */
		public string $country_id;

		/**
		 * @var string
		 */
		public string $name;

		/**
		 * @GAC\Relationship(table="locations.country", foreign_key="country_id", references="id", column="name")
		 * @var string|null
		 */
		public ?string $country_name = NULL;

		/**
		 * @var string
		 */
		protected string $created_at;

		/**
		 * @var array|string[]
		 */
		protected array $ignoredColumns = [ "country_name" ];

		/**
		 * @param string|null $country_id
		 * @param string|null $name
		 */
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
