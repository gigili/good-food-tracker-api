<?php

	namespace Gac\GoodFoodTracker\Modules\Country\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class CountryFailedDeletingException extends Exception
	{
		#[Pure]
		public function __construct() {
			parent::__construct("Failed to delete a country", 500);
		}
	}