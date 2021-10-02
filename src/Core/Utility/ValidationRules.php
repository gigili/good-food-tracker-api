<?php

	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Utility;

	class ValidationRules
	{
		public const REQUIRED    = "required";
		public const MIN_LENGTH  = "min_length";
		public const MAX_LENGTH  = "max_length";
		public const VALID_EMAIL = "valid_email";
		public const NUMERIC     = "numeric";
		public const VALID_UUID  = "valid_uuid";
		public const SAME_AS     = "same_as";
	}
