<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-31
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Core\Session;

	class Session
	{
		/**
		 * @var Session The only instance of the class
		 */
		private static Session $instance;

		/**
		 * @var bool State of the session class
		 */
		private bool $sessionState = false;

		public function __construct() { }

		/**
		 * Returns the instance of 'Session'.
		 * The session is automatically initialized if it wasn't.
		 *
		 * @return Session
		 **/
		public static function getInstance() : self {
			if ( !isset(self::$instance) ) {
				self::$instance = new self;
			}

			self::$instance->start_session();

			return self::$instance;
		}

		/**
		 * Starts a session if it wasn't already started
		 *
		 * @return bool Returns the status of the session
		 */
		public function start_session() : bool {
			$status = session_status();
			if ( $this->sessionState == false && $status == PHP_SESSION_NONE ) {
				$this->sessionState = session_start();
			}

			return $this->sessionState;
		}

		/**
		 * Stops the session
		 *
		 * @return bool Returns the status of the session
		 */
		public function stop_session() : bool {
			if ( $this->sessionState == true ) {
				$this->sessionState = !session_destroy();
				unset($_SESSION);
				return !$this->sessionState;
			}

			return false;
		}

		/**
		 * Stores the key, value pair in the session
		 *
		 * @param string $key Key under which to be stored
		 * @param mixed $value Value to be stored
		 */
		public function set(string $key, mixed $value) {
			$_SESSION[$key] = $value;
		}

		/**
		 * Gets value from the session
		 *
		 * @param string $key Key for which to return the value
		 * @param mixed|null $defaultValue Default value to be returned if the key doesn't exist in session
		 *
		 * @return mixed Returns value from the session or @see $defaultValue
		 */
		public function get(string $key, mixed $defaultValue = NULL) : mixed {
			return $_SESSION[$key] ?? $defaultValue;
		}

		/**
		 * Check if the session has the value for specified key set
		 *
		 * @param string $key Key to be checked in the session
		 *
		 * @return bool Returns whether the key is present in session or not
		 */
		public function has(string $key) : bool {
			return isset($_SESSION[$key]);
		}

		/**
		 * Removes the value from the session
		 *
		 * @param string $key Key for which to remove the value
		 */
		public function remove(string $key) {
			if ( $this->has($key) ) {
				unset($_SESSION[$key]);
			}
		}
	}