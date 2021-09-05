<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;


	use Exception;
	use JetBrains\PhpStorm\Pure;

	class InvalidTokenException extends Exception
	{

		/**
		 * InvalidTokenException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Invalid token", 401);
		}
	}