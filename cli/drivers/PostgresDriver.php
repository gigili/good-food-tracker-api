<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-13
	 * Project: Good Food Tracker - API
	 */

	include_once __DIR__ . "/../classes/autoload.php";
	include_once __DIR__ . "/DatabaseInterface.php";

	/**
	 * Class PostgresDriver
	 *
	 * Driver class for connecting to the Postgres database using PDO and pgsql
	 */
	class PostgresDriver implements DatabaseInterface
	{
		/**
		 * Instance of \PDO connection
		 *
		 * @var PDO
		 */
		private PDO $db;

		/**
		 * PostgresDriver constructor.
		 *
		 * @param string|null $dbHost Database host url/ip
		 * @param int|null $dbPort Database port
		 * @param string|null $dbUsername Database username
		 * @param string|null $dbPassword Database password
		 * @param string|null $db Database to connect to
		 */
		public function __construct(
			?string $dbHost = NULL,
			?int    $dbPort = NULL,
			?string $dbUsername = NULL,
			?string $dbPassword = NULL,
			?string $db = NULL
		) {
			$dbHost = $dbHost ?? $_ENV['args'][CLIArgs::HOST];
			$dbPort = $dbPort ?? $_ENV['args'][CLIArgs::PORT];
			$dbUsername = $dbUsername ?? $_ENV['args'][CLIArgs::USERNAME];
			$dbPassword = $dbPassword ?? $_ENV['args'][CLIArgs::PASSWORD];
			$db = $db ?? $_ENV['args'][CLIArgs::DATABASE];

			$this->db = new PDO("pgsql:dbname=$db host=$dbHost port=$dbPort", $dbUsername, $dbPassword);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		/**
		 * Method used for creating the migrations table in the database
		 *
		 * @return bool|string returns true if the table was created or an exception message if it fails
		 */
		public function initialize() : bool|string {
			$baseSQL = "DROP TABLE IF EXISTS migrations;
				CREATE TABLE migrations(
    				id SERIAL NOT NULL CONSTRAINT PK_Migrations_ID PRIMARY KEY,
					file_name VARCHAR(255) NOT NULL CONSTRAINT UQ_Migrations_FileName UNIQUE,
					file_timestamp bigint NOT NULL,
					executed_at TIMESTAMP NOT NULL CONSTRAINT DF_Migrations_ExecutedAt DEFAULT CURRENT_TIMESTAMP
				);";

			try {
				$this->db->beginTransaction();
				$this->db->exec($baseSQL);
				$this->db->commit();
			} catch ( PDOException | Exception $ex ) {
				$this->db->rollBack();
				return $ex->getMessage();
			}

			return true;
		}

		/**
		 * Method used for getting previously executed migrations from the database
		 *
		 * @param string|null $migrationFileName Name of the previously executed migration or null for all
		 *
		 * @return array Return a list of migrations from the database
		 */
		public function get_migrations(string|int|null $migrationFileOrID = NULL) : array {
			$query = "SELECT * FROM migrations";
			$params = NULL;
			try {
				if ( !is_null($migrationFileOrID) ) {
					if ( is_numeric($migrationFileOrID) ) {
						$query .= " WHERE id = ?";
					} else {
						$query .= " WHERE file_name = ?";
					}

					$params = [ $migrationFileOrID ];
				}

				$query .= " ORDER BY id DESC";

				$stm = $this->db->prepare($query);
				$stm->execute($params);

				return $stm->fetchAll(PDO::FETCH_OBJ);
			} catch ( PDOException | Exception ) {
				return [];
			}
		}

		/**
		 * Method used for running the migration based on the SQL from the migration up or down file
		 *
		 * @param string $sql SQL to be executed
		 *
		 * @return string|bool Returns true if the execution was success or an exception message if it fails
		 */
		public function run_migration(string $sql) : string|bool {
			try {
				$this->db->beginTransaction();
				$this->db->exec($sql);
				$this->db->commit();
			} catch ( PDOException | Exception $ex ) {
				$this->db->rollBack();
				return $ex->getMessage();
			}

			return true;
		}

		/**
		 * Method used for storing the information about the migration that was run
		 *
		 * @param string $direction The direction in which the migrations were run (up/dow)
		 * @param string $migrationFile The name of the migration file that was executed
		 *
		 * @return string|bool Returns true if the execution was success or an exception message if it fails
		 */
		public function store_migration_info(string $direction, string $migrationFile) : string|bool {
			try {
				$timestamp = explode("-", $migrationFile)[0];
				$this->db->beginTransaction();
				if ( $direction === "up" ) {
					$stm = $this->db->prepare("INSERT INTO migrations (file_name, file_timestamp) VALUES(?,?)");
					$stm->execute([ $migrationFile, $timestamp ]);
				} else {
					$stm = $this->db->prepare('DELETE FROM migrations WHERE file_name = ?');
					$stm->execute([ $migrationFile ]);
				}
				$this->db->commit();
			} catch ( PDOException | Exception $ex ) {
				$this->db->rollBack();
				return $ex->getMessage();
			}
			return true;
		}

		public function execute_query(string $query, array $params = null, bool $singleResult = false) : object|array {
			$stm = $this->db->prepare($query);
			$stm->execute($params);

			$result = $stm->fetchAll(PDO::FETCH_OBJ);
			if ( $singleResult ) return isset($result[0]) ? (object) $result[0] : (object) [];

			return $result;
		}
	}