<?php
	include_once __DIR__ . "/classes/autoload.php";
	include_once __DIR__ . "/drivers/autoload.php";

	use JetBrains\PhpStorm\NoReturn;

	$shortopts = "";

	$longopts = [
		"driver::",
		"host::",
		"port::",
		"username::",
		"password::",
		"database::",
		"folder::",
		"init::",
		"create::",
		"up::",
		"down::",
	];

	$options = getopt($shortopts, $longopts);
	main($options);

	/**
	 * @param $args
	 */
	#[NoReturn] function main($args) {
		$_ENV["args"] = $args;
		foreach ( $args as $key => $value ) {
			switch ( mb_strtolower($key) ) {
				case CLICommands::INIT:
					init_migrations();
				case CLICommands::CREATE:
					create_new_migration($value);
				case CLICommands::UP:
				case CLICommands::DOWN:
					migrate($key, !empty($value) ? mb_strtolower($value) : NULL);
			}
		}

		print( "Try using --help or -h to get help with this command" );
		exit(1);
	}

	/**
	 * @param int|string $key
	 * @param string|null $migrationName
	 */
	#[NoReturn] function migrate(int|string $key, string|null $migrationName = NULL) {
		if ( !isset($_ENV["args"][CLIArgs::DRIVER]) ) {
			output("No database driver specified", LogLevel::ERROR);
			exit(1);
		}

		try {
			output("Starting to migrate $key...");
			$name = $migrationName ?? "all";
			if ( $key == "up" ) cli_migrate_up($name);
		} catch ( Exception $ex ) {
			output("Migration failed because: {$ex->getMessage()}", LogLevel::ERROR);
			exit(1);
		}

		exit(0);
	}

	/**
	 * @param string $migrationName
	 *
	 * @throws Exception
	 */
	function cli_migrate_up(string $migrationName = "all") {
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

		output("Successfully executed $cnt migrations", LogLevel::SUCCESS);
	}

	/**
	 * @throws Exception
	 * @return array
	 */
	function get_migration_files() : array {
		$folder = $_ENV['args'][CLIArgs::FOLDER] ?? __DIR__ . '/migrations';
		if ( !is_dir($folder) ) throw new Exception("Migrations folder doesn't exist");

		$result = [];
		foreach ( glob($folder . '*.*') as $file ) {
			if ( $file === "." || $file === ".." ) continue;
			$result[] = $file;
		}

		return $result;
	}

	/**
	 * @param mixed $value
	 */
	#[NoReturn] function create_new_migration(mixed $value) {
		$migrationName = preg_replace("/\s/", "-", mb_strtolower($value) ?? "new-migration");
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

			fwrite($handle, file_get_contents("./templates/migration-template.php"));
			fclose($handle);

			$sqlName = "$name.sql";

			$resultUp = file_put_contents("$folder/sql/up/$sqlName", "Migration created on: " . date("Y-m-d H:i:s"));
			if ( $resultUp === false ) throw new Exception("Unable to create file $folder/sql/up/$sqlName");

			$resultDown = file_put_contents("$folder/sql/$sqlName", "Migration created on: " . date("Y-m-d H:i:s"));
			if ( $resultDown === false ) throw new Exception("Unable to create file $folder/sql/down/$sqlName");

			output("New migration $migrationName created successfully...");
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
	 *
	 */
	#[NoReturn] function init_migrations() {
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
			output($ex->getMessage(), LogLevel::ERROR);
			exit(1);
		}
	}

	/**
	 * @throws Exception
	 * @return DatabaseInterface
	 */
	function get_migration_driver() : DatabaseInterface {
		if ( !isset(DBDrivers::getConstants()[$_ENV['args'][CLIArgs::DRIVER]]) ) throw new Exception('Invalid driver selected');
		return new ( DBDrivers::getConstants()[$_ENV['args'][CLIArgs::DRIVER]] );
	}

	/**
	 * @param string $msg
	 * @param string $lvl
	 * @param bool $silent
	 * @param bool $newLine
	 */
	function output(string $msg, string $lvl = LogLevel::INFO, bool $silent = false, bool $newLine = true) {
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