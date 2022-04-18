<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth\Models;

	use Gac\GoodFoodTracker\Core\Exceptions\InvalidInstanceException;
	use Gac\GoodFoodTracker\Entity\UserEntity;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailNotSentException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\EmailTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\InvalidActivationKeyException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\InvalidDataProvidedException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\RegistrationFailedException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UsernameTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotActiveException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use JetBrains\PhpStorm\ArrayShape;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use Ramsey\Uuid\Uuid;
	use ReflectionException;

	class AuthModel
	{
		/**
		 * @throws UserNotFoundException
		 * @throws UserNotActiveException
		 */
		#[ArrayShape( [ "user" => "\\Gac\\GoodFoodTracker\\Entities\\UserEntity", "tokens" => "array" ] )]
		public static function login(
			mixed $username,
			mixed $password
		) : array {
			$userEntity = new UserEntity();
			$user = $userEntity->filter([ 'username' => $username, 'password' => $password ], true);

			if ( !$user instanceof UserEntity ) {
				throw new UserNotFoundException();
			}
			if ( !isset($user->id) ) {
				throw new UserNotFoundException();
			}
			if ( !$user->is_active() ) {
				throw new UserNotActiveException();
			}

			$tokens = generate_token($user->id, true);

			return [ "user" => $user, "tokens" => $tokens ];
		}

		/**
		 * @throws RegistrationFailedException
		 * @throws EmailTakenException
		 * @throws UsernameTakenException
		 * @throws EmailNotSentException
		 * @throws ReflectionException
		 * @throws InvalidInstanceException
		 */
		public static function register(string $name, string $email, string $username, string $password) : UserEntity {
			$userEntity = new UserEntity();
			$existingUsers = $userEntity->filter([ 'username' => $username, 'email' => $email ], useOr : true);

			foreach ( $existingUsers as $existingUser ) {
				if ( !$existingUser instanceof UserEntity ) {
					break;
				}
				if ( $existingUser->username == $username ) {
					throw new UsernameTakenException();
				}
				if ( $existingUser->email == $email ) {
					throw new EmailTakenException();
				}
			}

			$activationKey = str_replace('-', '', mb_substr(Uuid::uuid4(), 0, 10));

			$newUser = $userEntity;
			$newUser->name = $name;
			$newUser->email = $email;
			$newUser->username = $username;
			$newUser->set_password($password);
			$newUser->set_activation_key($activationKey);
			$user = $newUser->save();

			if ( !isset($user->id) ) {
				throw new RegistrationFailedException();
			}

			$activationLink = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/activate/$activationKey";
			$emailBody = "Dear $name<br/><br/>to confirm your account, please click on the button that says Confirm account or copy the link below it and open it in your browser. <br/><br/> Good Food Tracker team";
			$emailSent = send_email(
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

			//TODO: Should this throw an exception or return success with warning?
			if ( !$emailSent ) throw new EmailNotSentException();

			if ( ( $user instanceof UserEntity ) == false ) throw new InvalidInstanceException("Not a valid instance of UserEntity in " . __FILE__ . ' @ ' . __LINE__);
			return $user;
		}

		/**
		 * @param mixed $activationKey
		 *
		 * @throws InvalidActivationKeyException
		 * @throws ReflectionException
		 * @throws UserNotFoundException
		 */
		public static function verify_account(mixed $activationKey) {
			$userEntity = new UserEntity();
			$user = $userEntity->filter([ "activation_key" => $activationKey ], true);

			if ( !$user instanceof UserEntity ) {
				throw new UserNotFoundException();
			}
			if ( !isset($user->id) ) {
				throw new InvalidActivationKeyException();
			}

			$user->set_activation_key(NULL);
			$user->status = 1;
			$user->save();
		}

		/**
		 * Method used for generating and sending a password reset code
		 *
		 * @param string|null $emailOrUsername
		 *
		 * @throws EmailNotSentException
		 * @throws InvalidDataProvidedException
		 * @throws ReflectionException
		 * @throws UserNotFoundException
		 */
		public static function generate_password_reset_code(?string $emailOrUsername = NULL) {
			if ( is_null($emailOrUsername) ) {
				throw new InvalidDataProvidedException("Invalid username/email provided");
			}

			$userEntity = new UserEntity();
			$user = $userEntity->filter([ "email" => $emailOrUsername, "username" => $emailOrUsername ], true, true);

			if ( ( $user instanceof UserEntity ) === false ) {
				throw new UserNotFoundException();
			}
			if ( !isset($user->id) ) {
				throw new UserNotFoundException();
			}

			$passwordResetCode = str_replace("-", "", substr(UuidV4::uuid4(), 0, 10));
			$user->status = 0;
			$user->set_password_reset_code($passwordResetCode);
			$user->save();

			$activationLink = "$passwordResetCode";
			$emailBody = "Dear $user->name<br/><br/>to resset your password, please click on the button or copy the password reset code below and enter it in the app. <br/><br/> Good Food Tracker team";
			$emailSent = send_email(
				$user->email,
				'Password reset code',
				$emailBody,
				emailTemplate : [
					'file' => 'password_reset_code_email',
					'args' => [
						'emailPreview' => strip_tags($emailBody),
						'emailActivationLink' => $activationLink,
					],
				]
			);

			if ( !$emailSent ) {
				throw new EmailNotSentException();
			}
		}

		/**
		 * @param string $passwordResetCode
		 * @param string $newPassword
		 *
		 * @throws UserNotFoundException
		 * @throws ReflectionException
		 * @throws EmailNotSentException
		 */
		public static function reset_password(string $passwordResetCode, string $newPassword) {
			$userEntity = new UserEntity();

			$user = $userEntity->filter([ "password_reset_code" => $passwordResetCode ], true);

			if ( ( $user instanceof UserEntity ) === false ) {
				throw new UserNotFoundException();
			}
			if ( !isset($user->id) ) {
				throw new UserNotFoundException();
			}

			$user->set_password_reset_code(NULL);
			$user->set_password($newPassword);
			$user->status = 1;
			$user->save();

			$activationLink = "$passwordResetCode";
			$emailBody = "Dear $user->name<br/><br/>your password was reset successfully. <br/><br/> Good Food Tracker team";
			$emailSent = send_email(
				$user->email,
				'Password reset successfully',
				$emailBody,
				emailTemplate : [
					'file' => 'confirm_email',
					'args' => [
						'emailTitle' => 'Password reset successfully',
						'emailPreview' => strip_tags($emailBody),
						'emailConfirmText' => 'Reset password',
						'emailActivationLink' => $activationLink,
					],
				]
			);

			if ( !$emailSent ) {
				throw new EmailNotSentException();
			}
		}
	}
