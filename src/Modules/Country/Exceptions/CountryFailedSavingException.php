<?php

	namespace Gac\GoodFoodTracker\Modules\Country\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class CountryFailedSavingException extends Exception
	{
		#[Pure]
		public function __construct() {
			parent::__construct("Failed to save information about the country", 500);
		}
	}