<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Entities;

	use Gac\GoodFoodTracker\Core\App;
	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use JetBrains\PhpStorm\ArrayShape;
	use Ramsey\Uuid\Rfc4122\UuidV4;
	use ReflectionClass;
	use ReflectionException;
	use ReflectionProperty;

	abstract class Entity implements EntityInterface
	{
		/**
		 * @var string Entities table name in the database
		 */
		private string $table;

		/**
		 * @var string Entities primary key column in the database
		 */
		private string $primaryKey;

		/**
		 * @var Database Instance of database class
		 */
		protected Database $db;

		/**
		 * @var array List of table columns to be ignored during script generation
		 */
		protected array $ignoredColumns = [];

		/**
		 * @var array List of annotations for the instantiated class
		 */
		private array $annotations;

		/**
		 * @throws AppNotInitializedException
		 */
		public function __construct(string $table, string $primaryKey = "id") {
			$this->table = $table;
			$this->primaryKey = $primaryKey;
			$this->annotations = $this->get_annotations();
			$this->db = ( App::get_instance() )->get_db();
		}

		/**
		 * Method used for converting database result row into an instance of an Entity Class
		 *
		 * @param mixed $result Database result row
		 *
		 * @return Entity returns an instance of an Entity class with database columns converted into instance properties
		 */
		public function from_result(mixed $result) : self {
			$t = new $this();
			$reflection = new ReflectionClass($this);
			$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

			foreach ( $properties as $property ) {
				if ( $property->class === Entity::class ) {
					continue;
				}
				if ( isset($result->{$property->getName()}) ) {
					$t->{$property->getName()} = $result->{$property->getName()};
				}
			}

			return $t;
		}

		/**
		 * Method used for inserting/updating an instance of entity class
		 *
		 * @throws ReflectionException Throws an exception if the class or property does not exist.
		 */
		public function save() : self {
			$ref = new ReflectionClass($this);
			$properties = $ref->getProperties();

			if ( isset($this->{$this->primaryKey}) ) {
				$query = "UPDATE " . $this->table . " SET ";
				$params = [];

				foreach ( $properties as $property ) {
					if ( $property->class === Entity::class ) {
						continue;
					}
					if ( $property->getName() === 'ignoredColumns' ) {
						continue;
					}
					$column = $property->getName();
					if ( in_array($column, $this->ignoredColumns) ) {
						continue;
					}
					$rfProperty = new ReflectionProperty($property->class, $property->getName());
					$rfProperty->setAccessible(true);
					if ( !$rfProperty->isInitialized($this) ) {
						continue;
					}
					$value = $this->{$property->getName()};
					$query .= "$column = ?, ";

					if ( is_bool($value) ) {
						$value = $value === true ? "TRUE" : "FALSE";
					}

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
					if ( $property->class === Entity::class ) {
						continue;
					}
					if ( $property->getName() === "ignoredColumns" ) {
						continue;
					}
					$column = $property->getName();
					if ( in_array($column, $this->ignoredColumns) ) {
						continue;
					}
					$value = $this->{$property->getName()};
					$columns .= "$column, ";
					$values .= "?,";

					if ( is_bool($value) ) {
						$value = $value === true ? "TRUE" : "FALSE";
					}

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

			return $this->get($this->{$this->primaryKey});
		}

		/**
		 * Method used for deleting a database record based on the instance of an entity class
		 *
		 * @return Entity Returns the instance of an entity class that was deleted
		 */
		public function delete() : self {
			$id = $this->{$this->primaryKey};
			$query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
			$this->db->get_result($query, [ $id ]);
			return $this;
		}

		/**
		 * Method used for getting a single instance of en entity class from the database
		 *
		 * @param mixed $value Value used for search the database
		 * @param string|null $column Column name to be used in search the database
		 *
		 * @return Entity|array returns an instance of an entity class
		 */
		public function get(mixed $value, ?string $column = NULL) : Entity|array {
			$column = $column ? : $this->primaryKey;

			$queryParts = $this->generate_select_query();
			$annotations = $this->annotations;
			$where = 'WHERE ' . ( count($annotations) == 0 ? "$column = ?" : "$this->table.$column = ?" );
			$query = $queryParts["select"] . $queryParts["from"] . $queryParts["join"] . $where;

			return $this->from_result($this->db->get_result(
				query : $query,
				params : [ $value ],
				singleResult : true
			));
		}

		/**
		 * Method used to filter the database and returns a result array of entity instances
		 *
		 * @param mixed|array $filters List of columns (as keys) and values to be filtered by
		 * @param bool $singleResult If the result should return only the first row
		 * @param bool $useOr If the method should use AND or OR when there are multiple columns
		 * @param int $start Used for pagination
		 * @param int $limit Used for pagination
		 * @param bool $useLike If the method should use SQL LIKE statement or equal (=) in comparing columns and values
		 * @param array $ignoredLikedFields List of fields to be ignored when filtering data
		 * @param array|null $columns List of columns to be returned when filtering data
		 *
		 * @return Entity|array Returns a list of entities or a single entity if $singleResult is set to true
		 */
		public function filter(
			mixed  $filters = [],
			bool   $singleResult = false,
			bool   $useOr = false,
			int    $start = 0,
			int    $limit = 10,
			bool   $useLike = false,
			array  $ignoredLikedFields = [],
			?array $columns = NULL
		) : Entity|array {
			$queryParts = $this->generate_select_query($columns);
			$entity = new ( ( new ReflectionClass($this) )->getName() )();

			$whereConditions = "";
			$connectionOperand = $useOr ? 'OR' : 'AND';
			foreach ( $filters as $column => $value ) {
				$aliasedColumn = $column;
				if ( in_array($aliasedColumn, array_keys($this->annotations)) ) {
					$data = $this->annotations[$aliasedColumn][EntityAnnotations::Relationship];
					$aliasedColumn = "{$data["table"]}.$aliasedColumn";
				} else {
					$aliasedColumn = "$this->table.$aliasedColumn";
				}

				if ( $useLike ) {
					if ( !in_array($column, $ignoredLikedFields) ) {
						$filters[$column] = "%$value%";
					}
				}
				$whereConditions .= "$aliasedColumn " . ( $useLike ? "ILIKE" : "=" ) . " ? $connectionOperand ";
			}

			$whereConditions = rtrim($whereConditions, "$connectionOperand ");

			$where = empty($whereConditions) ? 'WHERE 1=1' : "WHERE $whereConditions";
			$queryLimit = "LIMIT $limit OFFSET $start";

			$query = $queryParts["select"] . $queryParts["from"] . $queryParts["join"] . $where . $queryLimit;

			$result = $this->db->get_result($query, array_values($filters), $singleResult);

			if ( is_object($result) ) {
				return $this->from_result($result);
			}

			if ( $singleResult ) {
				return isset($result[0]) ? $this->from_result($result[0]) : $entity;
			}

			$formattedResults = [];
			foreach ( $result as $res ) {
				$formattedResults[] = $this->from_result($res);
			}

			return $formattedResults;
		}

		/**
		 * Method used for getting all the annotations for the class, and it's properties
		 *
		 * @return array Returns a list of all the annotations
		 */
		private function get_annotations() : array {
			$r = new ReflectionClass($this);
			$properties = $r->getProperties();
			$allAnnotations = [];

			$matchPattern = '/@GAC\\\(.*?)[\r\n]/s';

			preg_match_all($matchPattern, $r->getDocComment(), $annotations);
			if ( count($annotations[1] ?? 0) > 0 ) {
				$allAnnotations[$r->getName()] = $annotations[1];
			}

			foreach ( $properties as $property ) {
				$doc = $property->getDocComment();
				preg_match_all($matchPattern, $doc, $annotations);
				if ( count($annotations[1] ?? 0) > 0 ) {
					preg_match_all("/[(,\s](.+?)=[\"'](.+?)[\"']/", $annotations[1][0], $matches);
					preg_match_all("/(.+?)\(/", $annotations[1][0], $annotationKeys);
					$data = [];
					for ( $index = 0; $index < count($matches[1]); $index++ ) {
						$data[trim($matches[1][$index])] = trim($matches[2][$index]);
					}
					$allAnnotations[$property->getName()][$annotationKeys[1][0]] = $data;
				}
			}

			return $allAnnotations;
		}

		/**
		 * Method used for generating parts of sql statements (select, from and join)
		 *
		 * @param string|array|null $columns List of columns to be returned when the query runs or all the public entity properties
		 *
		 * @return string[] Returns an array of generated sql parts
		 */
		#[ArrayShape( [ "select" => "string", "from" => "string", "join" => "string", ] )]
		private function generate_select_query(string|array|null $columns = NULL) : array {
			$annotations = $this->annotations;

			if ( is_string($columns) ) {
				$columns = [ $columns ];
			}

			if ( is_null($columns) ) {
				$select = 'SELECT ' . ( count($annotations) == 0 ? '*' : "$this->table.*" );
			} else {
				$select = 'SELECT ';
				foreach ( $columns as $c ) {
					if ( in_array($c, array_keys($annotations)) ) {
						continue;
					}
					$select .= "$this->table.$c, ";
				}
				$select = rtrim($select, ", ");
			}

			$from = "FROM $this->table";
			$join = '';

			if ( count($annotations) > 0 ) {
				foreach ( $annotations as $column => $keys ) {
					if ( !is_null($columns) && !in_array($column, $columns) ) {
						continue;
					}
					$data = $keys[EntityAnnotations::Relationship];
					$select .= ", {$data['table']}.{$data['column']} AS $column ";
					$join .= "LEFT JOIN {$data['table']} ON {$data['table']}.{$data['references']} = $this->table.{$data['foreign_key']} ";
				}
			}

			return [ "select" => "$select ", "from" => "$from ", "join" => "$join " ];
		}

		/**
		 * @return Database
		 */
		public function get_db() : Database {
			return $this->db;
		}
	}
