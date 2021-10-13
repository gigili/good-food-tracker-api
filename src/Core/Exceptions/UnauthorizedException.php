<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;

	use Exception;

	class UnauthorizedException extends Exception
	{
		protected $message = "You're not authorized to access this resource";
		protected $code    = 401;
	}