<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: API
	 */

	function dump($data = []) {
		echo json_encode($data);
	}

	function dd($data = []) {
		dump($data);
		die(1);
	}