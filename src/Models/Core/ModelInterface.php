<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Models\Core;

	interface ModelInterface
	{
		public static function get(mixed $value);

		public static function filter(mixed $filters, bool $singleResult = false, bool $useOr = false);

		public static function add(Model $model);

		public static function update(Model $model);

		public static function delete(Model $model);

		public static function from_result(mixed $result);
	}