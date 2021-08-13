<?php
	include_once "./CliClasses.php";
	include_once "./drivers/PostgresDriver.php";

	use JetBrains\PhpStorm\NoReturn;

	$shortopts = "";

	$longopts = array(
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
	);

	$options = getopt($shortopts, $longopts);
	main($options);

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
					migrate($key, mb_strtolower($value));
			}
		}

		print( "Try using --help or -h to get help with this command" );
		exit(0);
	}

	#[NoReturn] function migrate(int|string $key, array|bool|string|null $migrationName) {
		if ( !isset($_ENV["args"][CLIArgs::DRIVER]) ) {
			output("No database driver specified", LogLevel::ERROR);
			exit(1);
		}

		try {
			output("Starting to migrate $key...");
			$name = "1628858168-first-migration.php";
			$folder = $_ENV['args'][CLIArgs::FOLDER] ?? "./migrations";
			output("Found migration $name...");
			require_once "$folder/$name";
			output("Running migration $name...");
			$driver = new ( DBDrivers::getConstants()[$_ENV["args"][CLIArgs::DRIVER]] );
			migrate_up($driver);
			output("Executed migration $name...");
		}catch (Exception $ex){
			output($ex->getMessage(), LogLevel::ERROR);
		}
		exit(0);
	}

	#[NoReturn] function create_new_migration(mixed $value) {
		$migrationName = preg_replace("/\s/", "-", $_ENV["args"][CLICommands::CREATE] ?? "new-migration");
		output("Creating new migration $migrationName...");

		try {
			$now = time();
			$name = "$now-" . $migrationName . ".php";
			$folder = $_ENV['args'][CLIArgs::FOLDER] ?? "./migrations";

			if ( !file_exists($folder) ) {
				mkdir($folder, 0644, true);
			}

			if ( !file_exists($folder . "/sql") ) {
				mkdir($folder . "/sql", 0644, true);
			}

			$handle = fopen("$folder/$name", "w");
			if ( is_null($handle) || $handle === false ) {
				output("Unable to create new migration...");
				exit(0);
			} else {
				fwrite($handle, file_get_contents("./templates/migration-template.php"));
				fclose($handle);
			}

			$sqlNameUp = str_replace(".php", "-up.sql", $name);
			$handle = fopen("$folder/sql/$sqlNameUp", "w");
			fwrite($handle, "");
			fclose($handle);

			$sqlNameDown = str_replace(".php", "-down.sql", $name);
			$handle = fopen("$folder/sql/$sqlNameDown", "w");
			fwrite($handle, "");
			fclose($handle);

			output("New migration $migrationName created successfully...");
		} catch ( Exception $ex ) {
			output("Unable to create new migration...", LogLevel::ERROR);
		}
		exit(0);
	}

	#[NoReturn] function init_migrations() {
		if ( !isset($_ENV["args"][CLIArgs::DRIVER]) ) {
			output("No database driver specified", LogLevel::ERROR);
			exit(1);
		}

		output("Initializing migrations table...");
		output("Creating new DB driver...");
		try {
			$driver = new ( DBDrivers::getConstants()[$_ENV["args"][CLIArgs::DRIVER]] );
			output("DB driver created...", LogLevel::SUCCESS);
			$res = $driver->initialize();

			if ( $res === true ) {
				output("Migrations table created successfully", LogLevel::SUCCESS);
				exit(0);
			}

			output($res, LogLevel::ERROR);
		} catch ( Exception $ex ) {
			output($ex->getMessage(), LogLevel::ERROR);
		} finally {
			exit(0);
		}
	}

	function output(string $msg, string $lvl = LogLevel::INFO, bool $silent = false, bool $newLine = true) {
		if ( $newLine ) echo "\r\n";

		$color = "\e[37m";
		$prefix = "[INFO]";

		switch ( LogLevel::getConstants() ) {
			case LogLevel::SUCCESS:
				$color = "\e[92m";
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
	}