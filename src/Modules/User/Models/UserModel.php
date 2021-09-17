<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\User\Models;


	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Entity\UserEntity;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use Gac\GoodFoodTracker\Modules\User\Exceptions\InvalidSearchTermException;
	use Gac\Routing\Request;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use ReflectionException;

	class UserModel
	{

		/**
		 * @throws InvalidSearchTermException
		 */
		public static function get_users(
			?string $search = NULL,
			int $start = 0,
			int $limit = 10
		) : array|null {
			$userEntity = new UserEntity();

			if ( is_null($search) || empty($search) ) {
				throw new InvalidSearchTermException();
			}

			return $userEntity->filter(
				filters : [ "name" => $search, "email" => $search, "username" => $search ],
				singleResult : false,
				useOr : true,
				start : $start,
				limit : $limit,
				useLike : true,
				ignoredLikedFields : [ 'email' ]
			);
		}

		/**
		 * @throws InvalidUUIDException
		 */
		public static function get_user(string $userID) : UserEntity|array {
			if ( empty($userID) || !UuidV4::isValid($userID) ) throw new InvalidUUIDException();

			$userEntity = new UserEntity();
			return $userEntity->get($userID);
		}

		/**
		 * @throws ReflectionException
		 * @throws UserNotFoundException
		 */
		public static function update_user(Request $request, ?string $profileImage = NULL) : UserEntity {
			$userEntity = new UserEntity();
			$user = $userEntity->get($_SESSION['userID']);

			if ( is_null($user) || !isset($user->id) ) throw new UserNotFoundException();

			$user->name = $request->get('name');
			$user->email = $request->get('email');
			if ( !is_null($profileImage) ) $user->image = $profileImage;

			return $user->save();
		}
	}