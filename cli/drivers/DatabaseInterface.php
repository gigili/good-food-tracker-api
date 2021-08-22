<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-13
	 * Project: Good Food Tracker - API
	 */

	/**
	 * Interface DatabaseInterface
	 */
	interface DatabaseInterface
	{
		/**
		 * Method used for creating the migrations table in the database
		 *
		 * @return bool|string returns true if the table was created or an exception message if it fails
		 */
		public function initialize() : bool|string;

		/**
		 * Method used for getting previously executed migrations from the database
		 *
		 * @param string|null $migrationFileName Name of the previously executed migration or null for all
		 *
		 * @return array Return a list of migrations from the database
		 */
		public function get_migrations(string|null $migrationFileName = NULL) : array;

		/**
		 * Method used for running the migration based on the SQL from the migration up or down file
		 *
		 * @param string $sql SQL to be executed
		 *
		 * @return string|bool Returns true if the execution was success or an exception message if it fails
		 */
		public function run_migration(string $sql) : string|bool;

		/**
		 * Method used for storing the information about the migration that was run
		 *
		 * @param string $direction The direction in which the migrations were run (up/dow)
		 * @param string $migrationFile The name of the migration file that was executed
		 *
		 * @return string|bool Returns true if the execution was success or an exception message if it fails
		 */
		public function store_migration_info(string $direction, string $migrationFile) : string|bool;
	}