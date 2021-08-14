<?php

	interface DatabaseInterface
	{
		public function initialize();

		public function get_migrations();

		public function run_migration(string $sql) : string|bool;

		public function store_migration_info(string $direction, string $migrationFile) : string|bool;
	}