<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class InvalidInstanceException extends Exception
	{
		protected $code = 500;

		/**
		 * @param $message
		 */
		#[Pure]
		public function __construct(protected $message) {
			parent::__construct($this->message, $this->code);
		}
	}