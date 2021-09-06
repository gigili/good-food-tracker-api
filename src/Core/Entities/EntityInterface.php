<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-10
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Core\Entities;

	interface EntityInterface
	{

		/**
		 * @param mixed $value
		 * @param string|null $column
		 *
		 * @return object|array
		 */
		public function get(mixed $value, ?string $column = NULL) : object|array;

		/**
		 * @return object
		 */
		public function save() : Entity;

		/**
		 * @return Entity
		 */
		public function delete() : Entity;

		/**
		 * @param mixed $filters
		 * @param bool $singleResult
		 * @param bool $useOr
		 *
		 * @return object|array|null
		 */
		public function filter(
			mixed $filters,
			bool $singleResult = false,
			bool $useOr = false
		) : object|array|null;
	}