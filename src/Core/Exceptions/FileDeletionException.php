<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-3
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class FileDeletionException extends Exception
	{
		/**
		 * FileDeletionException constructor.
		 */
		#[Pure] public function __construct(?string $message = "There was an error while trying to delete a file", int $code = 500) {
			parent::__construct($message, $code);
		}
	}
