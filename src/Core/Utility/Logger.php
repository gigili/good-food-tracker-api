<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Utility;

	class Logger
	{
		public static function log(string $message = '', string $logFileName = 'app_log.log') {
			if ( is_dir(BASE_PATH . '/../logs') === false ) {
				mkdir(BASE_PATH . '/../logs', 0644);
			}

			$handle = fopen(BASE_PATH . "/../logs/$logFileName", 'a+');
			fwrite($handle, $message . "\n");
			fclose($handle);
		}

		public static function info(string $message = "", string $logFileName = "app_log.log") {
			$prefix = '[' . date('Y-m-d H:i:s') . ' | INFO] ';
			self::log($prefix . $message, $logFileName);
		}

		public static function warning(string $message = '', string $logFileName = 'app_log.log') {
			$prefix = '[' . date('Y-m-d H:i:s') . ' | WARNING] ';
			self::log($prefix . $message, $logFileName);
		}

		public static function error(string $message = '', string $logFileName = 'app_log.log') {
			$prefix = '[' . date('Y-m-d H:i:s') . ' | ERROR] ';
			self::log($prefix . $message, $logFileName);
		}

		public static function log_network_request(
			string            $message,
			array|object|null $response = NULL,
			string            $logFileName = 'app_log.log'
		) {
			self::log("=====================================================\n", $logFileName);
			self::log('Date: ' . date('Y-m-d H:i:s') . "\n", $logFileName);
			self::log("IP: {$_SERVER['REMOTE_ADDR']}\n", $logFileName);
			self::log("Request method: {$_SERVER['REQUEST_METHOD']}\n", $logFileName);
			self::log("Request params: \n" . json_encode($_REQUEST) . "\n", $logFileName);
			if ( !is_null($response) ) {
				self::log("Response: \n" . json_encode($response) . "\n", $logFileName);
			}
			self::log($message, $logFileName);
			self::log("=====================================================\n", $logFileName);
		}
	}
