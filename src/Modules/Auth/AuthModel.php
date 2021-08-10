<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;


	use Gac\GoodFoodTracker\Models\UserModel;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\RegistrationFailedException;
	use Ramsey\Uuid\Uuid;

	class AuthModel
	{

		public static function login(mixed $username, mixed $password) : UserModel {
			$user = UserModel::filter([ "username" => $username, "password" => $password ], true);
			return UserModel::from_result($user);
		}

		/**
		 * @throws RegistrationFailedException
		 */
		public static function register(string $name, string $email, string $username, string $password) : UserModel {
			$user = new UserModel($name, $email, $username);
			$user->id = Uuid::uuid4();
			$user->setPassword($password);
			$user->setActivationKey(str_replace('-', '', mb_substr(Uuid::uuid4(), 0, 10)));

			$result = UserModel::add($user);

			if ( is_null($result) ) {
				throw new RegistrationFailedException();
			}

			return $result;
		}
	}