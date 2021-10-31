<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-19
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Controllers;

	use Exception;
	use Gac\GoodFoodTracker\Core\App;
	use Gac\GoodFoodTracker\Core\Utility\Logger;

	class BaseController implements ControllerInterface
	{
		/**
		 * @var App Instance of the app class
		 */
		protected App $app;

		public function __construct() {
			try {
				$this->app = App::get_instance();
			} catch ( Exception $ex ) {
				Logger::error("Redis error: {$ex->getMessage()}");
			}
		}
	}
