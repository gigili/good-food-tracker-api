<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-19
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Controllers;


	use Exception;
	use Gac\GoodFoodTracker\Core\Utility\Logger;
	use Predis\Client as RedisClient;

	class BaseController implements ControllerInterface
	{
		/**
		 * @var RedisClient Instance of a redis cache
		 */
		protected RedisClient $redis;

		/**
		 * BaseController constructor.
		 *
		 * @param string|null $redisHost Host url for Redis cache
		 * @param int|null $redisPort
		 */
		public function __construct(?string $redisHost = NULL, ?int $redisPort = NULL) {
			try {
				$this->redis = new RedisClient([
					'host' => $redisHost ?? $_ENV['REDIS_HOST'],
					'port' => $redisPort ?? $_ENV['REDIS_PORT'],
				]);
			} catch ( Exception $ex ) {
				Logger::error("Redis error: {$ex->getMessage()}");
			}
		}
	}