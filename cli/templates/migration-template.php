<?php

	/**
	 * @throws Exception
	 */
	function migrate_up($driver) {
		$path = pathinfo(__FILE__, PATHINFO_DIRNAME) . "/sql/up";
		$file = pathinfo(__FILE__, PATHINFO_FILENAME) . ".sql";

		if ( !file_exists("$path/$file") ) throw new Exception("Migration file $path/$file not found.");
		$sql = file_get_contents("$path/$file");

		$migrationResult = $driver->run_migration($sql);
		if ( $migrationResult !== true ) throw new Exception($migrationResult);

		$migrationStoreResult = $driver->store_migration_info(CLICommands::UP, $file);
		if ( $migrationStoreResult !== true ) throw new Exception($migrationStoreResult);
	}

	/**
	 * @throws Exception
	 */
	function migrate_down($driver) {
		$path = pathinfo(__FILE__, PATHINFO_DIRNAME) . '/sql/down';
		$file = pathinfo(__FILE__, PATHINFO_FILENAME) . '.sql';

		if ( !file_exists("$path/$file") ) throw new Exception("Migration file $path/$file not found.");
		$sql = file_get_contents("$path/$file");

		$migrationResult = $driver->run_migration($sql);
		if ( $migrationResult !== true ) throw new Exception($migrationResult);

		$migrationStoreResult = $driver->store_migration_info(CLICommands::UP, $file);
		if ( $migrationStoreResult !== true ) throw new Exception($migrationStoreResult);
	}