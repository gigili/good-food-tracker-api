<?php

	namespace Gac\GoodFoodTracker\Modules\City\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class CityFailedDeletingException extends Exception
	{
		#[Pure]
		public function __construct() {
			parent::__construct("Failed to delete a city", 500);
		}
	}