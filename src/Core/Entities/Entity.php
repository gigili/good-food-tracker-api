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
	use ReflectionProperty;

	abstract class Entity implements EntityInterface
	{
		private string     $table;
		private string     $primaryKey;
		protected Database $db;

		public function __construct(string $table, string $primaryKey = "id") {
			$this->table = $table;
			$this->primaryKey = $primaryKey;
			$this->db = Database::getInstance();
		}

		public function from_result(mixed $result) : Entity {
			$reflection = new ReflectionClass($this);
			$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

			foreach ( $properties as $property ) {
				if ( $property->class === Entity::class ) continue;
				if ( isset($result->{$property->getName()}) ) {
					$this->{$property->getName()} = $result->{$property->getName()};
				}
			}

			return $this;
		}

		public function save() : Entity {
			$ref = new ReflectionClass($this);
			$properties = $ref->getProperties();

			if ( isset($this->{$this->primaryKey}) ) {
				$query = "UPDATE " . $this->table . " SET ";
				$params = [];

				foreach ( $properties as $property ) {
					if ( $property->class === Entity::class ) continue;
					$column = $property->getName();
					$value = $this->{$property->getName()};
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

				foreach ( $properties as $property ) {
					if ( $property->class === Entity::class ) continue;
					$column = $property->getName();
					$value = $this->{$property->getName()};
					$columns .= "$column, ";
					$values .= "?,";
					$params[] = $value;
				}

				$columns = rtrim($columns, ", ");
				$values = rtrim($values, ",");

				$query .= " ($columns) VALUES ($values) RETURNING " . $this->primaryKey;
			}

			$res = $this->db->get_result(query : $query, params : $params, singleResult : true);

			if ( isset($res->{$this->primaryKey}) ) {
				$this->{$this->primaryKey} = $res->{$this->primaryKey};
			}

			return $this;
		}

		public function delete() : Entity {
			$id = $this->{$this->primaryKey};
			$query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
			$this->db->get_result($query, [ $id ]);
			return $this;
		}

		public function get(mixed $value, ?string $column = NULL) : object|array {
			$column = $column ?: $this->primaryKey;
			$q = 'SELECT * FROM ' . $this->table . " WHERE $column = ?";
			return $this->from_result($this->db->get_result(
				query : $q,
				params : [ $value ],
				singleResult : true
			));
		}

		public function filter(
			mixed $filters = [],
			bool $singleResult = false,
			bool $useOr = false
		) : Entity|array|null {

			$query = 'SELECT * FROM ' . $this->table;
			$entity = new ( ( new ReflectionClass($this) )->getName() )();

			$query .= ' WHERE 1=1 AND';
			$connectionOperand = $useOr ? 'OR' : 'AND';

			foreach ( $filters as $column => $value ) {
				$query .= " $column = ? $connectionOperand ";
			}

			$query = rtrim($query, "$connectionOperand ");
			$result = $this->db->get_result($query, array_values($filters), $singleResult);

			if ( is_object($result) ) return $this->from_result($result);

			if ( $singleResult ) {
				return isset($result[0]) ? $this->from_result($result[0]) : $entity;
			}

			$formattedResults = [];
			foreach ( $result as $res ) {
				$formattedResults[] = $this->from_result($res);
			}

			return $formattedResults;
		}
	}