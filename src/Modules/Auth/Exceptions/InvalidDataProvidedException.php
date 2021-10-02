<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth\Exceptions;

	use Exception;
	use JetBrains\PhpStorm\Pure;

	class InvalidDataProvidedException extends Exception
	{
		/**
		 * InvalidDataProvidedException constructor.
		 */
		#[Pure] public function __construct(?string $message = NULL, ?int $code = 400) {
			parent::__construct($message ?? "Invalid data provided", $code);
		}
	}
