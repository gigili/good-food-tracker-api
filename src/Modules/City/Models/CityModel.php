<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-09-24
     * Project: Good Food Tracker - API
     */

    namespace Gac\GoodFoodTracker\Modules\City\Models;

    use Gac\GoodFoodTracker\Core\Entities\Entity;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
    use Gac\GoodFoodTracker\Entity\CityEntity;
    use Gac\GoodFoodTracker\Modules\City\Exceptions\CityNotFoundException;
    use Ramsey\Uuid\Rfc4122\UuidV4;
    use ReflectionException;

    class CityModel
    {
        public static function filter(?string $search = null, int $start = 0, int $limit = 10): Entity|array|null
        {
            $cityEntity = new CityEntity();
            return $cityEntity->filter([ "name" => $search ], start : $start, limit : $limit, useLike : true);
        }

        /**
         * @param string $cityID
         *
         * @throws CityNotFoundException
         * @throws InvalidUUIDException
         * @return CityEntity|array
         */
        public static function get(string $cityID): CityEntity|array
        {
            if (empty($cityID) || !UuidV4::isValid($cityID)) {
                throw new InvalidUUIDException();
            }

            $cityEntity = new CityEntity();
            $city = $cityEntity->get($cityID);

            if (($city instanceof CityEntity) === false || !isset($city->id)) {
                throw new CityNotFoundException();
            }

            return $city;
        }

        /**
         * @param string $name
         * @param string $countryID
         *
         * @throws ReflectionException
         * @return CityEntity
         */
        public static function add(string $name, string $countryID): CityEntity
        {
            $cityEntity = new CityEntity($countryID, $name);
            return $cityEntity->save();
        }

        /**
         * @throws InvalidUUIDException
         * @throws CityNotFoundException
         * @throws ReflectionException
         */
        public static function update(string $cityID, string $name): CityEntity|array
        {
            if (empty($cityID) || !UuidV4::isValid($cityID)) {
                throw new InvalidUUIDException();
            }

            $cityEntity = new CityEntity();
            $city = $cityEntity->get($cityID);

            if (is_null($city) || !isset($city->id)) {
                throw new CityNotFoundException();
            }

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
        public static function delete(string $cityID): Entity|array
        {
            if (empty($cityID) || !UuidV4::isValid($cityID)) {
                throw new InvalidUUIDException();
            }

            $cityEntity = new CityEntity();
            $city = $cityEntity->get($cityID);

            if (is_null($city) || !isset($city->id)) {
                throw new CityNotFoundException();
            }

            return $city->delete();
        }
    }
