<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-01
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

	/**
	 * RestaurantEntity Class
	 *
	 * @OA\Schema (
	 *  schema="RestaurantEntity",
	 *  type="object",
	 *  properties={
	 *  	@OA\Property(property="id", type="string"),
	 *  	@OA\Property(property="city_id", type="string"),
	 *  	@OA\Property(property="name", type="string"),
	 *  	@OA\Property(property="address", type="string", nullable=true),
	 *  	@OA\Property(property="phone", type="string", nullable=true),
	 *  	@OA\Property(property="email", type="string", nullable=true),
	 *  	@OA\Property(property="delivery", type="boolean"),
	 *  	@OA\Property(property="takeout", type="boolean"),
	 *  	@OA\Property(property="geo_lat", type="number", nullable=true),
	 *  	@OA\Property(property="geo_long", type="number", nullable=true),
	 *  	@OA\Property(property="city_name", type="string", nullable=true),
	 *  }
	 * )
	 */
	class RestaurantEntity extends Entity
	{
		public string  $id;
		public string  $city_id;
		public string  $name;
		public ?string $address  = NULL;
		public ?string $phone    = NULL;
		public ?string $email    = NULL;
		public bool    $delivery = false;
		public bool    $takeout  = false;
		public ?float  $geo_lat  = NULL;
		public ?float  $geo_long = NULL;

		/**
		 * @GAC\Relationship(table="locations.city", foreign_key="city_id", references="id", column="name")
		 * @var string | null
		 */
		public ?string $city_name = NULL;

		protected string $created_at;

		protected array $ignoredColumns = [ "city_name" ];

		public function __construct(?string $name = NULL, ?string $address = NULL) {
			$this->created_at = date("c");
			parent::__construct("places.restaurant");

			if ( !is_null($name) ) {
				$this->name = $name;
			}

			if ( !is_null($address) ) {
				$this->address = $address;
			}
		}
	}