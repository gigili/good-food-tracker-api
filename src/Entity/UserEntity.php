<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;
	use JetBrains\PhpStorm\ArrayShape;

	/**
	 * UserEntity class
	 *
	 * @OA\Schema (
	 *    schema="UserEntity",
	 *    properties={
	 *     @OA\Property(property="id", type="string"),
	 *     @OA\Property(property="name", type="string"),
	 *     @OA\Property(property="email", type="string"),
	 *     @OA\Property(property="username", type="string"),
	 *     @OA\Property(property="image", type="string", nullable=true)
	 *    }
	 * )
	 */
	class UserEntity extends Entity
	{
		public string     $id;
		public string     $name;
		public string     $email;
		public string     $username;
		public ?string    $image          = NULL;
		public int        $status         = 0;
		protected ?string $password;
		protected ?string $activation_key = NULL;

		/**
		 * UserEntity constructor.
		 *
		 * @param string|null $name
		 * @param string|null $email
		 * @param string|null $username
		 */
		public function __construct(?string $name = NULL, ?string $email = NULL, ?string $username = NULL) {
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

		public function set_activation_key(?string $activation_key) {
			$this->activation_key = $activation_key;
		}

		public function set_password(string $password) {
			$this->password = $password;
		}

		public function is_active() : bool {
			return $this->status === 1;
		}

		#[ArrayShape( [ "id" => "string", "name" => "string", "email" => "string", "username" => "string", "image" => "string" ] )] public function __serialize(
		) : array {
			return [
				"id" => $this->id,
				"name" => $this->name,
				"email" => $this->email,
				"username" => $this->username,
				"image" => __DIR__ . "/$this->image",
			];
		}

		public function __toString() : string {
			return $this->name;
		}
	}