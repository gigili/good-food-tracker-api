<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Utility\DB;


	use PDO;

	class Database
	{
		private static Database|null $instance = NULL;
		private PDO                  $db;

		private function __construct(
			?string $dbHost = NULL,
			?int $dbPort = NULL,
			?string $dbUsername = NULL,
			?string $dbPassword = NULL,
			?string $db = NULL
		) {
			$dbHost = $dbHost ?? $_ENV["DB_HOST"];
			$dbPort = $dbPort ?? $_ENV["DB_PORT"];
			$dbUsername = $dbUsername ?? $_ENV["DB_USERNAME"];
			$dbPassword = $dbPassword ?? $_ENV["DB_PASSWORD"];
			$db = $db ?? $_ENV['DB'];

			$this->db = new PDO("pgsql:dbname={$db} host={$dbHost} port={$dbPort}", $dbUsername, $dbPassword);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		public static function getInstance(
			?string $dbHost = NULL,
			?int $dbPort = NULL,
			?string $dbUsername = NULL,
			?string $dbPassword = NULL,
			?string $db = NULL
		) : Database {
			if ( self::$instance == NULL ) {
				self::$instance = new Database(
					$dbHost,
					$dbPort,
					$dbUsername,
					$dbPassword,
					$db,
				);
			}

			return self::$instance;
		}

		public static function execute_query(
			string $query,
			array $params = [],
			bool $singleResult = false
		) : array|object {
			$db = Database::getInstance()->db;
			$stm = $db->prepare($query);

			if ( count($params) > 0 ) {
				$stm->execute($params);
			} else {
				$stm->execute();
			}

			$result = $stm->fetchAll(PDO::FETCH_OBJ);
			return $singleResult === false ? $result : $result[0] ?? [];
		}
	}