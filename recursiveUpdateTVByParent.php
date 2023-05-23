<?php

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . "/vendor/autoload.php";

use Symfony\Component\Dotenv\Dotenv;
use Lavre\ModXCSV\Database;
use Lavre\ModXCSV\FileCommander;

$dotenv = new Dotenv();
$dotenv->load(ROOT_PATH . '/.env');

$database = new Database($_ENV['APP_HOST'], $_ENV['APP_USER'], $_ENV['APP_PASSWORD'], $_ENV['APP_DATABASE']);

$fileCommander = new FileCommander(__DIR__ . "/results/update.log");

$parent = 0;
$tv = 0;
$find = "";
$replace = "";

if ($tv === 0 or $tv < 0) {
    throw new Exception("\n Check your TV parameter. \n");
}

function getChilds($parent) : void
{
    global $database, $fileCommander, $tv, $find, $replace;

    $resources = $database->getResourcesByParent($parent);

    if (is_null($resources)) {
        echo "\n ID $parent don't have any childs \n";
        return;
    }

    foreach ($resources as $resource) {
        $tvValue = $database->getTemplateById($resource['id'], $tv);

        if (!is_null($tvValue)) {
            if ($tvValue['value'] == $find) {
                $database->updateTemplateById($tvValue['id'], $replace);
                echo "\n Resource " . $resource['id'] . " was updated. \n";
                $fileCommander->writeFileData("Resource " . $resource['id'] . " was updated.", newline: true);
            }
        }

        getChilds($resource['id']);
    }
}

getChilds($parent);
