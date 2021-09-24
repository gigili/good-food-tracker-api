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
	use ReflectionException;
	use ReflectionProperty;

	abstract class Entity implements EntityInterface
	{
		private string     $table;
		private string     $primaryKey;
		protected Database $db;
		protected array    $ignoredColumns = [];

		public function __construct(string $table, string $primaryKey = "id") {
			$this->table = $table;
			$this->primaryKey = $primaryKey;
			$this->db = Database::getInstance();
		}

		public function from_result(mixed $result) : Entity {
			$t = new $this;
			$reflection = new ReflectionClass($this);
			$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

			foreach ( $properties as $property ) {
				if ( $property->class === Entity::class ) continue;
				if ( isset($result->{$property->getName()}) ) {
					$t->{$property->getName()} = $result->{$property->getName()};
				}
			}

			return $t;
		}

		/**
		 * @throws ReflectionException
		 */
		public function save() : Entity {
			$ref = new ReflectionClass($this);
			$properties = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

			if ( isset($this->{$this->primaryKey}) ) {
				$query = "UPDATE " . $this->table . " SET ";
				$params = [];

				foreach ( $properties as $property ) {
					if ( $property->class === Entity::class ) continue;
					$column = $property->getName();
					if ( in_array($column, $this->ignoredColumns) ) continue;
					$rfProperty = new ReflectionProperty($property->class, $property->getName());
					$rfProperty->setAccessible(true);
					if ( !$rfProperty->isInitialized($this) ) continue;
					$value = $this->{$property->getName()};
					$query .= "$column = ?, ";
					$params[] = $value;
				}

				$query = rtrim($query, ", ");
				$query .= " WHERE " . $this->primaryKey . " = ?";
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
					if ( in_array($column, $this->ignoredColumns) ) continue;
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

		public function get(mixed $value, ?string $column = NULL) : Entity|array {
			$column = $column ? : $this->primaryKey;
			$q = 'SELECT * FROM ' . $this->table . " WHERE $column = ?";
			return $this->from_result($this->db->get_result(
				query : $q,
				params : [ $value ],
				singleResult : true
			));
		}

		public function filter(
			mixed $filters = [],
			bool  $singleResult = false,
			bool  $useOr = false,
			int   $start = 0,
			int   $limit = 10,
			bool  $useLike = false,
			array $ignoredLikedFields = []
		) : Entity|array|null {

			$query = 'SELECT * FROM ' . $this->table;
			$entity = new ( ( new ReflectionClass($this) )->getName() )();

			$query .= ' WHERE 1=1 AND';
			$connectionOperand = $useOr ? 'OR' : 'AND';

			foreach ( $filters as $column => $value ) {
				if ( $useLike ) {
					if ( !in_array($column, $ignoredLikedFields) ) {
						$filters[$column] = "%$value%";
					}
				}
				$query .= " $column " . ( $useLike ? "ILIKE" : "=" ) . " ? $connectionOperand ";
			}

			$query = rtrim($query, "$connectionOperand ");

			$query .= " LIMIT $limit OFFSET $start";

			//dd([$query, array_values($filters)]);
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