<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker;

	include_once '../vendor/autoload.php';

	use Exception;
	use Gac\Routing\Exceptions\RouteNotFoundException;
	use Gac\Routing\Request;
	use Gac\Routing\Routes;

	$routes = new Routes();
	try {
		$routes->add("/", function (Request $request) {
			$name = $request->get("name");
			$request->send([ "message" => "Hello " . ( $name ?? "World" ) ]);
		});

		require_once "./routes.php";

		$routes->handle();
	} catch ( RouteNotFoundException $ex ) {
		$routes->request
			->status(404)
			->send([
				'error' => [
					'message' => $ex->getMessage(),
					'field' => '',
				],
			]);
	} catch ( Exception $ex ) {
		$routes->request
			->status(500)
			->send([
				"error" => [
					"message" => $ex->getMessage(),
					"field" => "",
				],
			]);
	}