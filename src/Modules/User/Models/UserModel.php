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
	use Gac\GoodFoodTracker\Core\Utility\FileHandler;
	use Gac\GoodFoodTracker\Core\Utility\Logger;
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
				try {
					FileHandler::delete_image_from_disk(BASE_PATH.$user->image, true);
				} catch (FileDeletionException $e){
					$message = "Failed to delete image: ".BASE_PATH.$user->image." Message: ".$e->getMessage();
					Logger::error($message);
				}

				$user->image = str_replace(BASE_PATH, "", $profileImage);
			}

			return $user->save();
		}

		/**
		 * @throws InvalidTokenException
		 * @throws UserNotFoundException
		 * @throws EmailNotSentException
		 * @throws FileDeletionException
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

			$imagePath = BASE_PATH.$user->image;
			FileHandler::delete_image_from_disk($imagePath);
			$user->delete();

			$emailBody = "Dear $user->name<br/><br/> your account has been deleted successfully from our application. <br/><br/> Good Food Tracker team";
            if (!send_email(
                $user->email,
                "Account deleted successfully",
                $emailBody,
                emailTemplate : [
                    'file' => 'delete_user_email',
                    'args' => [ 'emailPreview' => strip_tags($emailBody) ]
                ]
            )) {
                throw new EmailNotSentException();
            }
		}
	}
