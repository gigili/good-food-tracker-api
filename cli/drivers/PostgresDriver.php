<?php
	include_once __DIR__ . "/../classes/autoload.php";
	include_once __DIR__ . "/DatabaseInterface.php";

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

		public function get_migrations(string|null $migrationFileName = NULL) : array {
			$query = "SELECT * FROM migrations";
			$params = NULL;
			try {
				if ( !is_null($migrationFileName) ) {
					$query .= " WHERE file_name = ?";
					$params = [ $migrationFileName ];
				}

				$stm = $this->db->prepare($query);
				$stm->execute($params);

				return $stm->fetchAll(PDO::FETCH_OBJ);
			} catch ( PDOException | Exception ) {
				return [];
			}
		}

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