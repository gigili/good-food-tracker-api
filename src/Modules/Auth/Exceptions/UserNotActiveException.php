<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class UserNotActiveException extends Exception
	{
		/**
		 * UserNotActiveException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Account not active", 423);
		}
	}
