<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-22
	 * Project: Good Food Tracker - API
	 */

	class CLICommands
	{
		public const INIT   = 'init';
		public const CREATE = 'create';
		public const UP     = 'up';
		public const DOWN   = 'down';

		static function getConstants() : array {
			$oClass = new ReflectionClass(__CLASS__);
			return $oClass->getConstants();
		}
	}