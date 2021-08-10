<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Models;

	use Gac\GoodFoodTracker\Models\Core\Model;
	use Gac\GoodFoodTracker\Utility\DB\Database;
	use JetBrains\PhpStorm\Pure;

	class UserModel extends Model
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
		 * UserModel constructor.
		 *
		 * @param string|null $name
		 * @param string|null $email
		 * @param string|null $username
		 */
		public function __construct(?string $name = NULL, ?string $email = NULL, ?string $username = NULL) {
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
			return $this->status === 1;
		}

		public static function get(mixed $value) : UserModel {
			$result = Database::execute_query(
				query : "SELECT * FROM users.user WHERE id = ?",
				params : [ $value ],
				singleResult : true
			);

			return self::from_result($result);
		}

		public static function filter(mixed $filters, bool $singleResult = false) : UserModel|array|null {
			$query = 'SELECT * FROM users."user" ';

			if ( empty($filters) ) {
				return NULL;
			}

			$query .= " WHERE ";

			foreach ( $filters as $column => $value ) {
				$query .= " $column = ? AND ";
			}

			$query = rtrim($query, "AND ");
			$result = Database::execute_query($query, array_values($filters), $singleResult);

			if ( $singleResult ) {
				return self::from_result($result);
			}

			$results = [];
			foreach ( $result as $row ) {
				$results[] = self::from_result($row);
			}

			return $results;
		}

		public static function add(UserModel|Model $model) : UserModel {
			$query = "INSERT INTO users.\"user\" (id, name, email, username, password, activation_key) VALUES (?,?,?,?,?,?) RETURNING id";
			$res = Database::execute_query(
				$query,
				[ $model->id, $model->name, $model->email, $model->username, $model->password, $model->activation_key ],
				true
			);
			return self::get($res->id);
		}

		public static function update($model) : UserModel {
			// TODO: Implement update() method.
			return $model;
		}

		public static function delete($model) : UserModel {
			// TODO: Implement delete() method.
			return $model;
		}

		#[Pure] public static function from_result($result) : UserModel {
			if ( is_null($result) ) {
				return new UserModel();
			}

			if ( is_array($result) ) {
				$result = (object) $result;
			}

			if ( empty($result) || !isset($result->id) ) {
				return new UserModel();
			}

			$user = new UserModel();

			foreach ( get_class_vars(UserModel::class) as $property => $defaultValue ) {
				if ( isset($result->{$property}) ) {
					$user->{$property} = $result->{$property};
				} else {
					$user->{$property} = $defaultValue;
				}
			}

			return $user;
		}
	}