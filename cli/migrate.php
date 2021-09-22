<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-13
	 * Project: Good Food Tracker - API
	 */

	include_once __DIR__ . "/classes/autoload.php";
	include_once __DIR__ . "/drivers/autoload.php";

	use JetBrains\PhpStorm\NoReturn;

	$shortOptions = "";

	$longOptions = [
		"driver::" => "Which database driver is going to be used to establish a database connection (available: PGSQL,MySQL,MSSQL).",
		"host::" => "Database host name or IP",
		"port::" => "Database port",
		"username::" => "Database login username",
		"password::" => "Database login password",
		"database::" => "On which database should the changes be applied to",
		"folder::" => "Location of the migrations folder (def: ./migrations)",
		"init::" => "Initialize migrations for the first time by creating the migrations table",
		"create::" => "Create a new migration",
		"up::" => "Run all the UP migrations, you can also do --up=\"migration-name\" to run a specific migration",
		"down::" => "Run all the DOWN migrations, you can also do --down=\"migration-id\" to run all the migrations up until the specified one (not running the specified one)",
		"help::" => "Prints this help text",
	];

	$options = getopt($shortOptions, array_keys($longOptions));
	main($options);

	/**
	 * Main function that gets called when the cli runs this file
	 *
	 * @param array|null $args arguments that get passed down from the cli
	 */
	#[NoReturn] function main(array|null $args) : void {
		if ( file_exists(__DIR__ . "/../.migration.conf") ) {
			output("Loading data from config file");
			import_config_data();
		}
		$_ENV["args"] = array_merge($args, $_ENV["args"]);

		foreach ( $args as $key => $value ) {
			switch ( mb_strtolower($key) ) {
				case CLICommands::INIT:
					init_migrations();
				case CLICommands::CREATE:
					create_new_migration($value);
				case CLICommands::UP:
				case CLICommands::DOWN:
					migrate($key, !empty($value) ? mb_strtolower($value) : NULL);
				case CLICommands::HELP:
					print_help_menu();
			}
		}

		print( "Try using --help\r\n" );
		exit(1);
	}

	/**
	 * Import configuration information from the .migration.conf file
	 */
	function import_config_data() {
		$file = file_get_contents(__DIR__ . "/../.migration.conf");
		$options = explode("\n", $file);

		foreach ( $options as $option ) {
			$item = explode("=", $option);
			if ( empty($item[0]) ) continue;
			$_ENV["args"][strtolower(trim($item[0]))] = trim($item[1]);
		}
	}

	/**
	 * Method used for printing out the help text in the cli
	 */
	#[NoReturn] function print_help_menu() {
		global $longOptions;
		print( "To run the migrations: " . PHP_EOL );
		print( "php migrate.php [arguments] " . PHP_EOL . PHP_EOL );

		print( "Arguments: " . PHP_EOL );
		foreach ( $longOptions as $key => $argument ) {
			$key = str_replace("::", "", $key);
			print( "--$key $argument" . PHP_EOL );
		}
		exit(0);
	}

	/**
	 * Method used for running migration either up or down based on the selected option
	 *
	 * @param int|string $key Direction of the migrations (up/down)
	 * @param string|null $migrationName Name of the migration file to execute or run all migration for up, and
	 * when doing down it should be the ID from the migrations table up until you wish to downgrade or all
	 */
	#[NoReturn] function migrate(int|string $key, string|null $migrationName = NULL) : void {
		if ( !isset($_ENV["args"][CLIArgs::DRIVER]) ) {
			output("No database driver specified", LogLevel::ERROR);
			exit(1);
		}

		try {
			output("Starting to migrate $key...");
			$nameOrID = $migrationName ?? "all";
			if ( $key == "up" ) cli_migrate_up($nameOrID);
			if ( $key == "down" ) cli_migrate_down($nameOrID === "all" ? NULL : $nameOrID);
		} catch ( Exception $ex ) {
			output("Migration failed because: {$ex->getMessage()}", LogLevel::ERROR);
			exit(1);
		}

		exit(0);
	}

	/**
	 * Method used to handle the up migration logic
	 *
	 * @param string $migrationName Name of the migration file to be execute or `all` for all un run migrations to execute
	 *
	 * @throws Exception Throws an exception when there is an error running migrations
	 */
	function cli_migrate_up(string $migrationName = "all") : void {
		$folder = $_ENV['args'][CLIArgs::FOLDER] ?? __DIR__ . '/migrations';
		output('Getting migration driver...');
		$driver = get_migration_driver();

		if ( $migrationName === "all" ) {
			$executedMigrations = $driver->get_migrations();
			$migrationFiles = get_migration_files();
		} else {
			$migrationName .= ".php";
			if ( !file_exists("$folder/$migrationName") ) throw new Exception("Migration file $migrationName not found");

			$sqlName = str_replace(".php", ".sql", $migrationName);
			$executedMigrations = $driver->get_migrations($sqlName);

			if ( count($executedMigrations) > 0 ) throw new Exception("Migration $migrationName already executed");
			$migrationFiles = [ "$folder/$migrationName" ];
		}

		if ( count($migrationFiles) === 0 ) {
			output("No migrations found...", LogLevel::WARNING);
			exit(0);
		}

		$cnt = 0;
		output("Executing " . count($migrationFiles) . " migration(s)");
		foreach ( $migrationFiles as $migrationFile ) {
			$migrationFileName = pathinfo($migrationFile, PATHINFO_FILENAME) . ".sql";
			if ( array_search($migrationFileName, array_column($executedMigrations, 'file_name')) !== false ) continue;
			if ( !file_exists($migrationFile) ) throw new Exception("Migration file $migrationFile not found");
			output("Found migration $migrationFileName...");

			require_once "$migrationFile";
			migrate_up($driver);

			output("Executed migration $migrationFileName successfully...", LogLevel::SUCCESS);
			$cnt++;
		}

		output("Successfully executed $cnt migration(s)", LogLevel::SUCCESS);
	}

	/**
	 * @throws Exception
	 */
	function cli_migrate_down(int $migrationID = NULL) : void {
		$folder = $_ENV['args'][CLIArgs::FOLDER] ?? __DIR__ . '/migrations';
		$driver = get_migration_driver();
		if ( is_null($migrationID) ) {
			$migrations = $driver->get_migrations();
		} else {
			$migrations = $driver->execute_query("SELECT * FROM migrations WHERE id > ?", [ $migrationID ]);
		}

		output("Found " . count($migrations) . " migration(s) to run");
		foreach ( $migrations as $migration ) {
			$migrationName = str_replace(".sql", ".php", $migration->file_name);
			if ( !file_exists("$folder/$migrationName") ) throw new Exception("Migration file $migrationName not found");
			output("Running down migration for $migrationName");
			include_once "$folder/$migrationName";
			migrate_down($driver);
			output("Down migration $migrationName executed successfully", LogLevel::SUCCESS);
		}
		output("Successfully executed " . count($migrations) . " migration(s)" , LogLevel::SUCCESS);
	}

	/**
	 * Method used to get all the migrations files in the migrations folder
	 *
	 * @throws Exception Throws an exception when the migration folder is not found
	 *
	 * @return array Returns a list of migration files or an empty array if there aren't any
	 */
	function get_migration_files() : array {
		$folder = $_ENV['args'][CLIArgs::FOLDER] ?? __DIR__ . '/migrations';
		if ( !is_dir($folder) ) throw new Exception("Migrations folder doesn't exist");

		$result = [];
		foreach ( glob($folder . '*.*') as $file ) {
			if ( $file === "." || $file === ".." ) continue;
			$result[] = $file;
		}

		sort($result);
		return $result;
	}

	/**
	 * Method used for creating new migration files
	 *
	 * @param string|null $migrationName Name of the new migration to be created, if none is provided it will use `new-migration` for name
	 */
	#[NoReturn] function create_new_migration(mixed $migrationName) : void {
		$migrationName = preg_replace("/\s/", "-", mb_strtolower($migrationName) ?? "new-migration");
		output("Creating new migration $migrationName...");

		$now = time();
		$name = "$now-" . $migrationName;
		$folder = $_ENV['args'][CLIArgs::FOLDER] ?? './migrations';

		try {
			if ( !is_dir($folder) ) {
				output("Creating migrations folder...");
				mkdir($folder, 0644, true);
			}

			if ( !is_dir($folder . "/sql/up/") ) {
				output('Creating migrations up folder...');
				mkdir($folder . "/sql/up", 0644, true);
			}

			if ( !is_dir($folder . '/sql/down/') ) {
				output('Creating migrations down folder...');
				mkdir($folder . '/sql/down', 0644, true);
			}

			$handle = fopen("$folder/$name.php", "w");
			if ( is_null($handle) || $handle === false ) throw new Exception("Unable to create file $folder/$name.php");

			fwrite($handle, file_get_contents(__DIR__ . "/templates/migration-template.php"));
			fclose($handle);

			$sqlName = "$name.sql";

			$resultUp = file_put_contents("$folder/sql/up/$sqlName", "-- Migration created on: " . date("Y-m-d H:i:s"));
			if ( $resultUp === false ) throw new Exception("Unable to create file $folder/sql/up/$sqlName");

			$resultDown = file_put_contents("$folder/sql/down/$sqlName",
				"-- Migration created on: " . date("Y-m-d H:i:s"));
			if ( $resultDown === false ) throw new Exception("Unable to create file $folder/sql/down/$sqlName");

			output("New migration $migrationName created successfully...", LogLevel::SUCCESS);
		} catch ( Exception $ex ) {
			if ( file_exists("$folder/$name.php") ) unlink("$folder/$name.php");
			if ( file_exists("$folder/sql/up/$name.sql") ) unlink("$folder/sql/up/$name.sql");
			if ( file_exists("$folder/sql/down/$name.sql") ) unlink("$folder/sql/down/$name.sql");

			output("Unable to create new migration because: {$ex->getMessage()}...", LogLevel::ERROR);
			exit(1);
		}

		exit(0);
	}

	/**
	 * Method used for creating the migrations table to track of all the migrations
	 */
	#[NoReturn] function init_migrations() : void {
		if ( !isset($_ENV["args"][CLIArgs::DRIVER]) ) {
			output("No database driver specified", LogLevel::ERROR);
			exit(1);
		}

		output("Initializing migrations table...");
		output("Creating new DB driver...");
		try {
			$driver = get_migration_driver();
			output("DB driver created...", LogLevel::SUCCESS);
			$res = $driver->initialize();

			if ( $res === true ) {
				output("Migrations table created successfully", LogLevel::SUCCESS);
				exit(0);
			}

			output($res, LogLevel::ERROR);
			exit(1);
		} catch ( Exception $ex ) {
			$cls = new ReflectionClass($ex);
			output("[{$cls->getShortName()}] " . $ex->getMessage(), LogLevel::ERROR);
			exit(1);
		}
	}

	/**
	 * Method used for getting the database driver based on the cli argument
	 *
	 * @throws Exception Throws an exception when it can't find the specified database driver
	 *
	 * @return DatabaseInterface Returns an instance of a selected database driver class
	 */
	function get_migration_driver() : DatabaseInterface {
		if ( !isset(DBDrivers::getConstants()[$_ENV['args'][CLIArgs::DRIVER]]) ) throw new Exception('Invalid driver selected');
		return new ( DBDrivers::getConstants()[$_ENV['args'][CLIArgs::DRIVER]] );
	}

	/**
	 * Method used for showing preformatted messages with colors in the cli
	 *
	 * @param string $msg Message to be printed
	 * @param string $lvl Type of message being printed (info, warning, error...)
	 * @param bool $silent Should the output be hidden unless it's level is error
	 * @param bool $newLine Should it output a new line after the message
	 */
	function output(string $msg, string $lvl = LogLevel::INFO, bool $silent = false, bool $newLine = true) : void {
		$color = "\e[37m";
		$prefix = "[INFO]";

		switch ( $lvl ) {
			case LogLevel::SUCCESS:
				$color = "\e[32m";
				$prefix = "[SUCCESS]";
				break;

			case LogLevel::WARNING:
				$color = "\e[93m";
				$prefix = "[WARNING]";
				break;

			case LogLevel::ERROR:
				$color = "\e[91m";
				$prefix = "[ERROR]";
				break;

			case LogLevel::INFO:
				$color = "\e[37m";
				$prefix = "[INFO]";
				break;
		}

		if ( $silent && $lvl !== LogLevel::ERROR ) return;
		print "$color$prefix $msg \e[0m";
		if ( $newLine ) print "\r\n";
	}