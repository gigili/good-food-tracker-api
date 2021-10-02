<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\User\Models;

	use Gac\GoodFoodTracker\Core\Entities\Entity;
	use Gac\GoodFoodTracker\Core\Exceptions\FileDeletionException;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidTokenException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Entity\UserEntity;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailNotSentException;
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
			int     $start = 0,
			int     $limit = 10
		) : array|null {
			$userEntity = new UserEntity();

			$filters = [];
			if ( !empty($search) ) {
				$filters = [ 'name' => $search, 'email' => $search, 'username' => $search ];
			}

			return $userEntity->filter(
				filters : $filters,
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
			if ( empty($userID) || !UuidV4::isValid($userID) ) {
				throw new InvalidUUIDException();
			}

			$userEntity = new UserEntity();
			return $userEntity->get($userID);
		}

		/**
		 * @throws ReflectionException
		 * @throws UserNotFoundException
		 */
		public static function update_user(Request $request, ?string $profileImage = NULL) : Entity {
			$userEntity = new UserEntity();
			$user = $userEntity->get($_SESSION['userID']);

			if ( ( $user instanceof UserEntity ) === false || !isset($user->id) ) throw new UserNotFoundException();

			$user->name = $request->get('name');
			$user->email = $request->get('email');
			if ( !is_null($profileImage) ) {
				$user->image = str_replace(BASE_PATH, "", $profileImage);
			}

			return $user->save();
		}

		/**
		 * @throws InvalidTokenException
		 * @throws UserNotFoundException
		 * @throws EmailNotSentException
		 */
		public static function delete_account() {
			if ( !isset($_SESSION["userID"]) ) {
				throw new InvalidTokenException();
			}
			$userID = $_SESSION["userID"];

			$userEntity = new UserEntity();
			$user = $userEntity->get($userID);
			if ( ( $user instanceof UserEntity ) === false ) {
				throw new UserNotFoundException();
			}
			static::delete_image_from_disk($user['image'], false);
			$user->delete();

			//TODO: send an account deleted notification email with a proper template
			if ( !send_email(
				$user->email,
				"Account deleted successfully",
				"Dear $userEntity->name, your account has been deleted successfully from our application."
			) ) {
				throw new EmailNotSentException();
			}
		}

		/**
		 * @throws FileDeletionException
		 */
		private static function delete_image_from_disk(?string $imageName, bool $throwExceptionOnFailure = false): void {
			$imagePath = $_ENV('UPLOAD_PATH');

			if(file_exists($imagePath.$imageName)) {
				$status = unlink($imagePath.$imageName);

				if($throwExceptionOnFailure && !$status){
					throw new FileDeletionException();
				}
			}
		}
	}
