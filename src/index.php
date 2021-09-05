<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-08-08
	 * Project: Good Food Tracker - API
	 */

	#declare( strict_types=1 );

	namespace Gac\GoodFoodTracker;

	session_start();
	date_default_timezone_set('Europe/Belgrade');

	include_once '../vendor/autoload.php';

	use Dotenv\Dotenv;
	use Exception;
	use Gac\GoodFoodTracker\Core\Exceptions\AppNotInitializedException;
	use Gac\Routing\Exceptions\RouteNotFoundException;
	use Gac\Routing\Request;
	use Gac\Routing\Routes;

	$routes = new Routes();
	try {
		$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
		$dotenv->load();

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
	} catch ( AppNotInitializedException $ex ) {
		$routes->request
			->status(500)
			->send([
				'error' => [
					'message' => "The app wasn't initialized properly {$ex->getMessage()}",
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