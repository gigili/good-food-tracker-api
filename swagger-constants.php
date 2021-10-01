<?php

    /**
     * Author: Igor Ilić <github@igorilic.net>
     * Date: 2021-09-17
     * Project: Good Food Tracker - API
     */

    use Dotenv\Dotenv;

    include_once './vendor/autoload.php';
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    defined("DEV_API_URL") or define("DEV_API_URL", "http://localhost:{$_ENV['APACHE_PORT']}");
    defined("PROD_API_URL") or define("PROD_API_URL", "https://gft.igorilic.net/api");
