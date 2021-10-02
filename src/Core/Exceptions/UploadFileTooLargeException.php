<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-15
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class UploadFileTooLargeException extends Exception
	{
		/**
		 * UploadFileTooLargeException constructor.
		 */
		#[Pure] public function __construct(?string $message = "File size was too large", int $code = 413) {
			parent::__construct($message, $code);
		}
	}
