<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-22
	 * Project: Good Food Tracker - API
	 */

	class LogLevel
	{
		public const INFO    = 'info';
		public const SUCCESS = 'success';
		public const WARNING = 'warning';
		public const ERROR   = 'error';

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}