<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entity;

	use Gac\GoodFoodTracker\Core\Entities\Entity;

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
		public ?string    $image               = NULL;
		public int        $status              = 0;
		public ?string    $role_id             = NULL;
		protected ?string $password;
		protected ?string $activation_key      = NULL;
		protected ?string $password_reset_code = NULL;

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

		public function __toString() : string {
			return $this->name;
		}

		public function set_activation_key(?string $activation_key) {
			$this->activation_key = $activation_key;
		}

		public function set_password(string $password) {
			$this->password = $password;
		}

		public function is_active() : bool {
			return ( $this->status === 1 && is_null($this->activation_key) );
		}

		/**
		 * @param string|null $password_reset_code
		 */
		public function set_password_reset_code(?string $password_reset_code) : void {
			$this->password_reset_code = $password_reset_code;
		}

		/**
		 * Returns the value that indicates which role the user has assigned
		 *
		 * @return int  Return the user's role level
		 */
		public function get_user_role() : int {
			if ( is_null($this->role_id) ) return 0;

			$roleEntity = new RoleEntity();
			$role = $roleEntity->get($this->role_id);
			if ( ( $role instanceof RoleEntity ) == false ) return 0;

			return $role->level;
		}
	}
