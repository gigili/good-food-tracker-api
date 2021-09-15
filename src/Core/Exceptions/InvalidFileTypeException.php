<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;


	use Exception;
	use JetBrains\PhpStorm\Pure;

	class InvalidFileTypeException extends Exception
	{

		/**
		 * InvalidFileTypeException constructor.
		 */
		#[Pure] public function __construct(?string $message = NULL, int $code = 500) {
			parent::__construct($message, $code);
		}
	}