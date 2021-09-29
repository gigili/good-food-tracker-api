<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-22
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Country\Models;


	use Gac\GoodFoodTracker\Core\Entities\Entity;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Entity\CountryEntity;
	use Gac\GoodFoodTracker\Modules\Country\Exceptions\CountryNotFoundException;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use ReflectionException;

	class CountryModel
	{

		public static function filter(?string $search = NULL, int $start = 0, int $limit = 10) : array|null {
			$countryEntity = new CountryEntity();
			return $countryEntity->filter(
				filters : is_null($search) ? [] : [ 'name' => $search, 'code' => $search ],
				singleResult : false,
				useOr : true,
				start : $start,
				limit : $limit,
				useLike : true
			);
		}

		/**
		 * @throws CountryNotFoundException
		 * @throws InvalidUUIDException
		 */
		public static function get_country(string $countryID) : array|Entity {
			if ( !UuidV4::isValid($countryID) ) throw new InvalidUUIDException();

			$countryEntity = new CountryEntity();
			$country = $countryEntity->get($countryID);

			if ( is_null($country) || !isset($country->id) ) {
				throw new CountryNotFoundException();
			}

			return $country;
		}

		/**
		 * @param string|null $code *
		 *
		 * @throws ReflectionException
		 */
		public static function add_country(string $name, ?string $code = NULL) : CountryEntity {
			$countryEntity = new CountryEntity($name);
			$countryEntity->code = $code;
			return $countryEntity->save();
		}

		/**
		 * @throws ReflectionException
		 * @throws CountryNotFoundException
		 * @throws InvalidUUIDException
		 */
		public static function update_country(
			string  $countryID,
			string  $name,
			?string $code = NULL
		) : array|CountryEntity {
			if ( empty($countryID) || !UuidV4::isValid($countryID) ) throw new InvalidUUIDException();
			$countryEntity = new CountryEntity();
			$country = $countryEntity->get($countryID);

			if ( is_null($country) ) throw new CountryNotFoundException();

			$country->name = $name;
			$country->code = $code;
			return $country->save();
		}

		/**
		 * @throws CountryNotFoundException
		 */
		public static function delete_country(string $countryID) : array|CountryEntity {
			$countryEntity = new CountryEntity();
			$country = $countryEntity->get($countryID);

			if ( is_null($country) || !isset($country->id) ) throw new CountryNotFoundException();
			return $country->delete();
		}
	}