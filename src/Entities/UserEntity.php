<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entities;

	use Gac\GoodFoodTracker\Entities\Core\Entity;
	use JetBrains\PhpStorm\Pure;

	class UserEntity extends Entity
	{
		public string     $id;
		public string     $name;
		public string     $email;
		public string     $username;
		public ?string    $image          = NULL;
		protected ?string $password;
		protected ?string $activation_key = NULL;

		/**
		 * UserEntity constructor.
		 *
		 * @param string|null $name
		 * @param string|null $email
		 * @param string|null $username
		 */
		#[Pure] public function __construct(?string $name = NULL, ?string $email = NULL, ?string $username = NULL) {
			parent::__construct("users.user");

			if ( !is_null($name) ) {
				$this->name = $name;
			}

			if ( !is_null($email) ) {
				$this->email = $email;
			}

			if ( !is_null($username) ) {
				$this->username = $username;
			}
		}

		public function setActivationKey($activation_key) {
			$this->activation_key = $activation_key;
		}

		public function setPassword(string $password) {
			$this->password = $password;
		}

		public function isActive() : bool {
			return true; //TODO: Fix this
		}

		#[Pure] protected function from_result(mixed $result) : UserEntity {
			if ( is_null($result) ) {
				return new UserEntity();
			}

			if ( is_array($result) ) {
				$result = (object) $result;
			}

			if ( empty($result) || !isset($result->id) ) {
				return new UserEntity();
			}

			$user = new UserEntity();

			foreach ( get_class_vars(UserEntity::class) as $property => $defaultValue ) {
				if ( in_array($property, $this->ignoredProperties) ) continue;

				if ( isset($result->{$property}) ) {
					$user->{$property} = $result->{$property};
				} else {
					$user->{$property} = $defaultValue;
				}
			}

			return $user;
		}
	}