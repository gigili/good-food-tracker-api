<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;


	use Gac\GoodFoodTracker\Models\UserModel;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\RegistrationFailedException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UsernameTakenException;
	use Ramsey\Uuid\Uuid;

	class AuthModel
	{

		public static function login(mixed $username, mixed $password) : UserModel {
			$user = UserModel::filter([ "username" => $username, "password" => $password ], true);
			return UserModel::from_result($user);
		}

		/**
		 * @throws RegistrationFailedException
		 * @throws EmailTakenException
		 * @throws UsernameTakenException
		 */
		public static function register(string $name, string $email, string $username, string $password) : UserModel {
			$existingUsers = UserModel::filter([ "username" => $username, "email" => $email ], useOr : true);

			foreach ( $existingUsers as $existingUser ) {
				if ( !$existingUser instanceof UserModel ) break;
				if ( $existingUser->email == $email ) throw new EmailTakenException();
				if ( $existingUser->username == $username ) throw new UsernameTakenException();
			}

			$user = new UserModel($name, $email, $username);
			$user->id = Uuid::uuid4();
			$user->setPassword($password);
			$user->setActivationKey(str_replace('-', '', mb_substr(Uuid::uuid4(), 0, 10)));

			$result = UserModel::add($user);

			if ( !isset($result->id) ) throw new RegistrationFailedException();

			//TODO: send account activation email

			return $result;
		}
	}