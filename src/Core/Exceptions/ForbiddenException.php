<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;

	use Exception;

	class ForbiddenException extends Exception
	{
		protected $message = "You do not have access to this resource";
		protected $code    = 403;
	}