<?php

	namespace Gac\GoodFoodTracker\Modules\City\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class CityFailedSavingException extends Exception
	{
		#[Pure]
		public function __construct() {
			parent::__construct("Failed to save information about the city", 500);
		}
	}