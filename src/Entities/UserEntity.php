<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entities;

	use Gac\GoodFoodTracker\Entities\Core\Entity;
	use JetBrains\PhpStorm\Pure;
	use ReflectionClass;
	use ReflectionProperty;

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

		protected function from_result(mixed $result) : UserEntity {
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

			$reflection = new ReflectionClass(UserEntity::class);
			$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach ( $properties as $property ) {
				if ( isset($result->{$property->getName()}) ) {
					$user->{$property->getName()} = $result->{$property->getName()};
				}
			}

			return $user;
		}
	}