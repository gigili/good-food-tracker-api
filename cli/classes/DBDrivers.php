<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-22
	 * Project: Good Food Tracker - API
	 */

	class DBDrivers
	{
		public const PGSQL = 'PostgresDriver';
		public const MYSQL = 'MySQLDriver';
		public const MSSQL = 'MSSQLDriver';

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}