<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Entities;

	use Gac\GoodFoodTracker\Core\DB\Database;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use ReflectionClass;

	abstract class Entity implements EntityInterface
	{
		private string $table;
		private string $primaryKey;

		public function __construct(string $table, string $primaryKey = "id") {
			$this->table = $table;
			$this->primaryKey = $primaryKey;
		}

		abstract protected function from_result(mixed $result) : Entity;

		public function save() : object {
			$ref = new ReflectionClass($this);
			$props = $ref->getProperties();

			if ( isset($this->{$this->primaryKey}) ) {
				$query = "UPDATE " . $this->table . " SET ";
				$params = [];

				foreach ( $props as $prop ) {
					if ( $prop->class === Entity::class ) continue;
					$column = $prop->getName();
					$value = $this->{$prop->getName()};
					$query .= "$column = ?, ";
					$params[] = $value;
				}

				$query = rtrim($query, ", ");
				$query .= "WHERE " . $this->primaryKey . " = ?";
				$params[] = $this->{$this->primaryKey};

			} else {
				$query = "INSERT INTO " . $this->table;
				$this->{$this->primaryKey} = UuidV4::uuid4();
				$columns = "";
				$values = "";
				$params = [];

				foreach ( $props as $prop ) {
					if ( $prop->class === Entity::class ) continue;
					$column = $prop->getName();
					$value = $this->{$prop->getName()};
					$columns .= "$column, ";
					$values .= "?,";
					$params[] = $value;
				}

				$columns = rtrim($columns, ", ");
				$values = rtrim($values, ",");

				$query .= " ($columns) VALUES ($values) RETURNING " . $this->primaryKey;
			}

			return Database::execute_query(
				query : $query,
				params : $params,
				singleResult : true
			);
		}

		public function delete() : Entity {
			$id = $this->{$this->primaryKey};
			$query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
			Database::execute_query($query, [ $id ]);
			return $this;
		}

		public function get(mixed $value, ?string $column = NULL) : object|array {
			$column = $column ?: $this->primaryKey;
			$q = 'SELECT * FROM ' . $this->table . " WHERE $column = ?";
			return $this->from_result(Database::execute_query(
				query : $q,
				params : [ $value ],
				singleResult : true
			));
		}

		public function filter(
			mixed $filters,
			bool  $singleResult = false,
			bool  $useOr = false
		) : Entity|array|null {
			$query = 'SELECT * FROM ' . $this->table;

			if ( empty($filters) ) {
				return NULL;
			}

			$query .= ' WHERE ';
			$connectionOperand = $useOr ? 'OR' : 'AND';

			foreach ( $filters as $column => $value ) {
				$query .= " $column = ? $connectionOperand ";
			}

			$query = rtrim($query, "$connectionOperand ");
			$result = Database::execute_query($query, array_values($filters), $singleResult);

			if ( is_object($result) ) return $this->from_result($result);

			if ( $singleResult && count($result) > 0 ) {
				return $this->from_result($result[0]);
			}

			$formattedResults = [];
			foreach ( $result as $res ) {
				$formattedResults[] = $this->from_result($res);
			}

			return $formattedResults;
		}
	}