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
$fileCommander = new FileCommander(ROOT_PATH . "/results/get-tv-param-result.csv");

$f = fopen(__DIR__ . "/" . $_ENV['FILE_NAME'], 'r+');

$condition = '`id`, `pagetitle`, `parent`';

while ($data = fgetcsv($f)) {
    if ($pagetitle = $data[0]) {
        $subResource = $resource = $database->getResourceByPagetitle($pagetitle, $condition);
        $images = [];

        if ($resource) {

            for ($i = 0; $i < $_ENV['MIN_COUNT']; $i++) {
                $image = $database->getTemplateById($resource['id'], $_ENV['TV_PARAM']);

                if (!is_null($image)) {
                    array_push($images, $image['value']);
                    $subResource = $resource = $database->getResourceById($resource['parent'], $condition);
                } else {
                    while (is_null($image)) {
                        $image = $database->getTemplateById($subResource['parent'], $_ENV['TV_PARAM']);
                        $subResource = $database->getResourceById($subResource['parent'], $condition);

                        if (is_null($subResource) and is_null($image)) {
                            array_push($images, 'image is undefined');
                            break;
                        }
                    }
                    array_push($images, $image['value']);
                }

                if (count($images) < 1) {
                    echo $i--;
                }
            }
        }
        $fileCommander->writeFileData($pagetitle . "," . implode(',', $images), newline: true);
    }
}
