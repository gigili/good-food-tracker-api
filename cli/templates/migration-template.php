<?php

	function migrate_up($driver) {
		try {
			$path = pathinfo(__FILE__, PATHINFO_DIRNAME) . "/sql";
			$file = pathinfo(__FILE__, PATHINFO_FILENAME) ."-up.sql";
			$sql = file_get_contents("$path/$file");
			$driver->execute_query($sql);
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	function migrate_down($driver) {
		try {
			$path = pathinfo(__FILE__, PATHINFO_DIRNAME) . "/sql";
			$file = pathinfo(__FILE__, PATHINFO_FILENAME) ."-down.sql";
			$sql = file_get_contents("$path/$file");
			$driver->execute_query($sql);
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}