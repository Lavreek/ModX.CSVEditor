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
$fileCommander = new FileCommander(ROOT_PATH . "/results/get-description-result.csv");

$f = fopen(__DIR__ . "/" . $_ENV['FILE_NAME'], 'r+');

$condition = '`parent`, `introtext`';

function getDescription($pagetitle, $condition) {
    global $database;

    if (is_numeric($pagetitle)) {
        $resource = $database->getResourceById($pagetitle, $condition);
    } else {
        $resource = $database->getResourceByPagetitle($pagetitle, $condition);
    }

    if (is_null($resource)) {
        return "resource is undefined";
    }

    if (!is_null($resource['introtext'])) {
        if (!empty($resource['introtext'])) {
            return $resource['introtext'];
        }
    }

    $parent = $database->getResourceById($resource['parent']);

    if (!is_null($parent)) {
        var_dump($resource);

        return getDescription($parent['id'], $condition);
    }
}

while ($data = fgetcsv($f)) {

    if ($pagetitle = $data[0]) {
        $introtext = getDescription($pagetitle, $condition);

        $fileCommander->writeFileData($pagetitle . ",\"" . str_replace(["\r", "\t", "\n", ','], ' ', $introtext) . "\"", newline: true);
    }
}
