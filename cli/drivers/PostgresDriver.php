<?php

	include_once "./CliClasses.php";
	include_once "./drivers/DatabaseInterface.php";

	class PostgresDriver implements DatabaseInterface
	{
		private PDO $db;

		public function __construct(
			?string $dbHost = NULL,
			?int $dbPort = NULL,
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

		public function initialize() : bool|string {
			$baseSQL = "CREATE TABLE IF NOT EXISTS migrations(
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

		public function get_migrations() { }

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
	}