<?php

	interface DatabaseInterface
	{
		public function initialize();
		public function get_migrations();
	}