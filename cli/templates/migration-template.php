<?php

	/**
	 * Method used for executing up migration for a specified file
	 *
	 * @throws Exception Throws an exception when there is an error executing a migration
	 */
	function migrate_up(DatabaseInterface $driver) : void {
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
	 * Method used for executing down migration for a specified file
	 *
	 * @throws Exception Throws an exception when there is an error executing a migration
	 */
	function migrate_down(DatabaseInterface $driver) : void {
		$path = pathinfo(__FILE__, PATHINFO_DIRNAME) . '/sql/down';
		$file = pathinfo(__FILE__, PATHINFO_FILENAME) . '.sql';

		if ( !file_exists("$path/$file") ) throw new Exception("Migration file $path/$file not found.");
		$sql = file_get_contents("$path/$file");

		$migrationResult = $driver->run_migration($sql);
		if ( $migrationResult !== true ) throw new Exception($migrationResult);

		$migrationStoreResult = $driver->store_migration_info(CLICommands::DOWN, $file);
		if ( $migrationStoreResult !== true ) throw new Exception($migrationStoreResult);
	}