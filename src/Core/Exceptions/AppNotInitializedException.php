<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;

	use Exception;

	class AppNotInitializedException extends Exception
	{
		protected $message = "The app wasn't initialized properly";
		protected $code    = 500;
	}
