<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-13
	 * Project: good-food-tracker-api
	 */

	namespace Gac\GoodFoodTracker\Modules\Review\Exceptions;

	use Exception;

	class ReviewNotFoundException extends Exception
	{
		protected $message = "Review not found";
		protected $code    = 404;
	}