<?php

namespace Lavre;

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . "/vendor/autoload.php";

use Symfony\Component\Dotenv\Dotenv;
use Lavre\ModXCSV\Database;
use Lavre\ModXCSV\FileCommander;

$dotenv = new Dotenv();
$dotenv->load(ROOT_PATH . '/.env');

$database = new Database($_ENV['APP_HOST'], $_ENV['APP_USER'], $_ENV['APP_PASSWORD'], $_ENV['APP_DATABASE']);
$fileCommander = new FileCommander(ROOT_PATH . "/results/result-pagetitles.csv");


function getResources($id) {
    global $database;
    global $fileCommander;

    $resources = $database->getResourcesByParent($id);

    if (is_null($resources)) {
        return;
    }

    foreach ($resources as $resourceItem) {
        if (preg_match('#BRAS#', $resourceItem['pagetitle'])) {
            $fileCommander->writeFileData($resourceItem['pagetitle'], newline: true);
        }

        getResources($resourceItem['id']);
    }
}

getResources(26497);
