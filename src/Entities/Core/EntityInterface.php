<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Entities\Core;

	interface EntityInterface
	{

		public function get(mixed $value, ?string $column = NULL) : object|array;

		public function save() : object;

		public function delete() : Entity;

		public function filter(
			mixed $filters,
			bool $singleResult = false,
			bool $useOr = false
		) : object|array|null;

		/*public function get_by(mixed $column, mixed $value, bool $singleResult = false);*/
	}