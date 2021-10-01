<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-10-01
     * Project: good-food-tracker-api
     */

    namespace Gac\GoodFoodTracker\Modules\Restaurant\Models;

    use Gac\GoodFoodTracker\Core\Entities\Entity;
    use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
    use Gac\GoodFoodTracker\Entity\RestaurantEntity;
    use Gac\GoodFoodTracker\Modules\Restaurant\Exceptions\RestaurantNotFoundExceptions;
    use Gac\Routing\Request;
    use Ramsey\Uuid\Rfc4122\UuidV4;
    use ReflectionException;

    class RestaurantModel
    {
        public static function filter(mixed $search, mixed $start, mixed $limit): Entity|array
        {
            $restaurantEntity = new RestaurantEntity();

            return $restaurantEntity->filter(
                filters : [ "name" => $search, "address" => $search ],
                useOr : true,
                start : $start,
                limit : $limit,
                useLike : true
            );
        }

        /**
         * @throws InvalidUUIDException
         * @throws RestaurantNotFoundExceptions
         */
        public static function get(string $restaurantID): RestaurantEntity
        {
            if (empty($restaurantID) || !UuidV4::isValid($restaurantID)) {
                throw new InvalidUUIDException();
            }

            $restaurantEntity = new RestaurantEntity();
            $restaurant = $restaurantEntity->get($restaurantID);

            if (($restaurant instanceof RestaurantEntity) === false || !isset($restaurant->id)) {
                throw new RestaurantNotFoundExceptions();
            }

            return $restaurant;
        }

        /**
         * @throws ReflectionException
         * @throws InvalidUUIDException
         * @throws RestaurantNotFoundExceptions
         */
        public static function create_or_update(Request $request, ?string $restaurantID): RestaurantEntity
        {
            $name = $request->get("name");
            $address = $request->get("address");
            $phone = $request->get("phone");
            $email = $request->get("email");
            $cityID = $request->get("cityID");
            $delivery = $request->get("delivery");
            $takeout = $request->get("takeout");
            $geo_lat = $request->get("geo_lat");
            $geo_long = $request->get("geo_long");

            if (is_null($cityID) || !UuidV4::isValid($cityID)) {
                throw new InvalidUUIDException();
            }

            $restaurant = new RestaurantEntity($name, $address);

            if (!is_null($restaurantID)) {
                $restaurant->id = $restaurantID;
            }

            $restaurant->city_id = $cityID;
            $restaurant->phone = $phone;
            $restaurant->email = $email;
            $restaurant->delivery = $delivery;
            $restaurant->takeout = $takeout;
            $restaurant->geo_lat = $geo_lat;
            $restaurant->geo_long = $geo_long;

            $result = $restaurant->save();

            if (($result instanceof RestaurantEntity) == false) {
                throw new RestaurantNotFoundExceptions();
            }

            return $result;
        }

        /**
         * @throws InvalidUUIDException
         * @throws RestaurantNotFoundExceptions
         */
        public static function delete(string $restaurantID): RestaurantEntity
        {
            if (empty($restaurantID) || !UuidV4::isValid($restaurantID)) {
                throw new InvalidUUIDException();
            }

            $restaurantEntity = new RestaurantEntity();
            $restaurant = $restaurantEntity->get($restaurantID);

            if (($restaurant instanceof RestaurantEntity) === false || !isset($restaurant->id)) {
                throw new RestaurantNotFoundExceptions();
            }

            return $restaurant->delete();
        }
    }
