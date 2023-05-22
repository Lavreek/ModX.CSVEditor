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
$fileCommander = new FileCommander(ROOT_PATH . "/results/result.csv");

$f = fopen(__DIR__ . "/" . $_ENV['APP_FILE_NAME'], 'r+');

$condition = '`pagetitle`, `parent`';

while ($data = fgetcsv($f)) {

    if ($pagetitle = $data[0]) {
        $path = "/" . $pagetitle;

        $resource = $database->getResourceByPagetitle($pagetitle, $condition);

        while ($resource['parent'] != 0) {
            var_dump($resource);
            $resource = $database->getResourceById($resource['parent'], $condition);
            $path = "/" . $resource['pagetitle'] . $path;
        }

        $fileCommander->writeFileData($path, newline: true);
    }
}
