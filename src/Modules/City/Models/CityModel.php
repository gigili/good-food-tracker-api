<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-24
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\City\Models;

	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Core\Entities\Entity;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Entity\CityEntity;
	use Gac\GoodFoodTracker\Modules\City\Exceptions\CityNotFoundException;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use ReflectionException;

	class CityModel
	{
		public static function filter(?string $search = NULL, int $start = 0, int $limit = 10) : Entity|array|null {
			$cityEntity = new CityEntity();
			$query = "
				SELECT city.*, country.name AS country_name  FROM locations.city AS city 
				LEFT JOIN locations.country AS country ON city.country_id = country.id
				WHERE city.name ILIKE ? 
				LIMIT ? OFFSET ?
			";

			$search = "$search%";
			$tmpResult = Database::execute_query($query, [ $search, $limit, $start ]);
			$result = [];

			foreach ( $tmpResult as $row ) {
				array_push($result, $cityEntity->from_result($row));
			}

			return $result;
		}

		/**
		 * @param string $cityID
		 *
		 * @throws CityNotFoundException
		 * @throws InvalidUUIDException
		 * @return CityEntity|array
		 */
		public static function get(string $cityID) : CityEntity|array {
			if ( empty($cityID) || !UuidV4::isValid($cityID) ) throw new InvalidUUIDException();

			$cityEntity = new CityEntity();
			$query = '
				SELECT city.*, country.name AS country_name  FROM locations.city AS city 
				LEFT JOIN locations.country AS country ON city.country_id = country.id
				WHERE city.id = ?
			';

			$result = Database::execute_query($query, [ $cityID ], singleResult : true);
			$city = $cityEntity->from_result($result);

			if ( is_null($city) || !isset($city->id) ) throw new CityNotFoundException();

			return $city;
		}

		/**
		 * @param string $name
		 * @param string $countryID
		 *
		 * @throws ReflectionException
		 * @return CityEntity
		 */
		public static function add(string $name, string $countryID) : CityEntity {
			$cityEntity = new CityEntity($countryID, $name);
			return $cityEntity->save();
		}

		/**
		 * @throws InvalidUUIDException
		 * @throws CityNotFoundException
		 * @throws ReflectionException
		 */
		public static function update(string $cityID, string $name) : CityEntity|array {
			if ( empty($cityID) || !UuidV4::isValid($cityID) ) throw new InvalidUUIDException();

			$cityEntity = new CityEntity();
			$city = $cityEntity->get($cityID);

			if ( is_null($city) || !isset($city->id) ) throw new CityNotFoundException();

			$city->name = $name;
			return $city->save();
		}

		/**
		 * @param string $cityID
		 *
		 * @throws CityNotFoundException
		 * @throws InvalidUUIDException
		 *
		 * @return Entity|array
		 */
		public static function delete(string $cityID) : Entity|array {
			if ( empty($cityID) || !UuidV4::isValid($cityID) ) throw new InvalidUUIDException();

			$cityEntity = new CityEntity();
			$city = $cityEntity->get($cityID);

			if ( is_null($city) || !isset($city->id) ) throw new CityNotFoundException();

			return $city->delete();
		}
	}