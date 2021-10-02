<?php

	namespace Gac\GoodFoodTracker\Modules\Auth\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class EmailNotSentException extends Exception
	{
		/**
		 * EmailNotSentException constructor.
		 */
		#[Pure] public function __construct() {
			parent::__construct("Unable to send email", 500);
		}
	}
