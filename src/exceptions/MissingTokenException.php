<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\exceptions;


	use Exception;
	use JetBrains\PhpStorm\Pure;

	class MissingTokenException extends Exception
	{

		/**
		 * MissingTokenException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Missing token", 401);
		}
	}