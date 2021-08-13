<?php
	include_once "./CliClasses.php";
	include_once "./drivers/DatabaseInterface.php";

	class PostgresDriver implements DatabaseInterface
	{
		private PDO $db;

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

			$this->db = new PDO("pgsql:dbname={$db} host={$dbHost} port={$dbPort}", $dbUsername, $dbPassword);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		public function execute_query(
			string $query,
			array  $params = [],
			bool   $singleResult = false
		) : array|object {
			$stm = $this->db->prepare($query);

			if ( count($params) > 0 ) {
				$stm->execute($params);
			} else {
				$stm->execute();
			}

			$result = $stm->fetchAll(PDO::FETCH_OBJ);
			return $singleResult === false ? $result : $result[0] ?? [];
		}

		public function get_migrations() { }

		public function initialize() : bool|string {
			$baseSQL = "CREATE TABLE IF NOT EXISTS migrations(
					file_name VARCHAR(255) NOT NULL CONSTRAINT UQ_Migrations_FileName UNIQUE,
					file_timestamp TIMESTAMP NOT NULL,
					executed_at TIMESTAMP NOT NULL CONSTRAINT DF_Migrations_ExecutedAt DEFAULT CURRENT_TIMESTAMP
				);";

			try {
				$this->db->beginTransaction();
				$this->execute_query($baseSQL);
				$this->db->commit();
			} catch ( PDOException | Exception $ex ) {
				$this->db->rollBack();
				return $ex->getMessage();
			}

			return true;
		}
	}