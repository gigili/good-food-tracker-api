<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class InvalidActivationKeyException extends Exception
	{
		/**
		 * InvalidActivationKeyException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Invalid activation key provided", 412);
		}
	}
