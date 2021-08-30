<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;


	use Gac\GoodFoodTracker\Entities\UserEntity;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\RegistrationFailedException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UsernameTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotActiveException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use JetBrains\PhpStorm\ArrayShape;
	use Ramsey\Uuid\Uuid;

	class AuthModel
	{

		/**
		 * @throws UserNotFoundException
		 * @throws UserNotActiveException
		 */
		#[ArrayShape( [ "user" => "\\Gac\\GoodFoodTracker\\Entities\\UserEntity", "tokens" => "array" ] )] public static function login(
			mixed $username,
			mixed $password
		) : array {
			$userEntity = new UserEntity();
			$user = $userEntity->filter([ 'username' => $username, 'password' => $password ], true);

			$u = new UserEntity();
			$u->save();

			if ( !$user instanceof UserEntity ) throw new UserNotFoundException();
			if ( !isset($user->id) ) throw new UserNotFoundException();

			if ( !$user->isActive() ) throw new UserNotActiveException();

			$tokens = generate_token($user->id, true);

			return [ "user" => $user, "tokens" => $tokens ];
		}

		/**
		 * @throws RegistrationFailedException
		 * @throws EmailTakenException
		 * @throws UsernameTakenException
		 */
		public static function register(string $name, string $email, string $username, string $password) : UserEntity {
			$userEntity = new UserEntity();
			$existingUsers = $userEntity->filter([ 'username' => $username, 'email' => $email ], useOr : true);

			foreach ( $existingUsers as $existingUser ) {
				if ( !$existingUser instanceof UserEntity ) break;
				if ( $existingUser->username == $username ) throw new UsernameTakenException();
				if ( $existingUser->email == $email ) throw new EmailTakenException();
			}

			$activationKey = str_replace('-', '', mb_substr(Uuid::uuid4(), 0, 10));
			$user = new UserEntity($name, $email, $username);
			$user->setPassword($password);
			$user->setActivationKey($activationKey);
			$result = $user->save();

			if ( !isset($user->id) && !isset($result->id) ) throw new RegistrationFailedException();

			$activationLink = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/activate/$activationKey";
			$emailBody = "Dear $name<br/><br/>to confirm your account, please click on the button that says Confirm account or copy the link below it and open it in your browser. <br/><br/> Did You Buy It? team";
			send_email(
				$email,
				"Confirm your account",
				$emailBody,
				emailTemplate : [
					'file' => 'confirm_email',
					'args' => [
						'emailTitle' => "Confirm your account",
						'emailPreview' => strip_tags($emailBody),
						'emailConfirmText' => "Confirm your account",
						'emailActivationLink' => $activationLink,
					],
				]
			);

			return $user;
		}
	}