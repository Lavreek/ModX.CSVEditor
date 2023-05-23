<?php

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . "/vendor/autoload.php";

use Symfony\Component\Dotenv\Dotenv;
use Lavre\ModXCSV\Database;

$dotenv = new Dotenv();
$dotenv->load(ROOT_PATH . '/.env');

$database = new Database($_ENV['APP_HOST'], $_ENV['APP_USER'], $_ENV['APP_PASSWORD'], $_ENV['APP_DATABASE']);

$tv = 0;
$find = "";
$replace = "";

if ($tv === 0 or $tv < 0) {
    throw new Exception("\n Check your TV parameter. \n");
}

$database->updateTemplateValueByCondition($tv, $replace, "`value` = '$find'");
