<?php

	class CLICommands
	{
		public const INIT   = "init";
		public const CREATE = "create";
		public const UP     = "up";
		public const DOWN   = "down";

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}

	class CLIArgs
	{
		public const DRIVER   = "driver";
		public const HOST     = "host";
		public const PORT     = "port";
		public const USERNAME = "username";
		public const PASSWORD = "password";
		public const DATABASE = "database";
		public const FOLDER   = "folder";

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}

	class LogLevel
	{
		public const INFO    = "info";
		public const SUCCESS = "success";
		public const WARNING = "warning";
		public const ERROR   = "error";

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}

	class DBDrivers
	{
		public const PGSQL = "PostgresDriver";
		public const MYSQL = "MySQLDriver";
		public const MSSQL = "MSSQLDriver";

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}