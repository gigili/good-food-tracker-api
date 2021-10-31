<?php
	declare( strict_types=1 );

	namespace Gac\GoodFoodTracker\Core;

	use Dotenv\Dotenv;
	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\GoodFoodTracker\Core\Session\Session;
	use Gac\GoodFoodTracker\Core\Utility\Logger;
	use Gac\Routing\Exceptions\CallbackNotFound;
	use Gac\Routing\Exceptions\RouteNotFoundException;
	use Gac\Routing\Routes;
	use Predis\Client as PredisClient;

	class App
	{
		private static ?App     $instance;
		protected ?PredisClient $redis;
		protected ?Routes       $routes;
		protected ?Database     $db;
		public Session          $session;

		public function __construct(string $envFile = "") {
			$dotenv = Dotenv::createImmutable(BASE_PATH . "/../$envFile");
			$dotenv->load();

			Logger::log("Instance of " . App::class . " created ");

			self::$instance = $this;
			$this->session = Session::getInstance();
		}

		/**
		 * @throws CallbackNotFound
		 * @throws RouteNotFoundException
		 */
		public function run() {
			$this->routes->handle();
		}

		/**
		 * Gets the instance of the App class
		 *
		 * @throws AppNotInitializedException
		 */
		public static function get_instance() : App {
			if ( is_null(self::$instance) ) throw new AppNotInitializedException;
			return self::$instance;
		}

		public function get_db() : ?Database {
			return $this->db;
		}

		public function set_db(?Database $db) {
			$this->db = $db;
		}

		public function set_routes(?Routes $routes) {
			$this->routes = $routes;
		}

		public function get_redis() : ?PredisClient {
			return $this->redis;
		}

		public function set_redis(?PredisClient $redis) {
			$this->redis = $redis;
		}
	}