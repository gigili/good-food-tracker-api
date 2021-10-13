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
	use Predis\Client as RedisClient;

	class BaseController implements ControllerInterface
	{
		/**
		 * @var RedisClient Instance of a redis cache
		 */
		protected RedisClient $redis;

		public function __construct() {
			try {
				$this->redis = ( App::get_instance() )->get_redis();
			} catch ( Exception $ex ) {
				Logger::error("Redis error: {$ex->getMessage()}");
			}
		}
	}
