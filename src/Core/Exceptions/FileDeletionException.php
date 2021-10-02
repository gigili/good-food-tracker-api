<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
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
		#[Pure] public function __construct(?string $message = "File not found", int $code = 404) {
			parent::__construct($message, $code);
		}
	}
