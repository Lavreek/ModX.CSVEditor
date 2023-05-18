<?php

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . "/vendor/autoload.php";

use Lavre\ModXCSV\Database;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(ROOT_PATH . '/.env');

require_once ROOT_PATH . "/src/Database.php";
require_once ROOT_PATH . "/src/FileCommander.php";

$database = new Database($_ENV['APP_HOST'], $_ENV['APP_USER'], $_ENV['APP_PASSWORD'], $_ENV['APP_DATABASE']);

$f = fopen(__DIR__ . "/" . $_ENV['APP_FILE_NAME'], 'r+');

$csvCommander = new FileCommander($f);

['header' => $header, 'rows' => $rows] = $csvCommander->getFileData();

foreach ($rows as $row) {
    if ($resource = $database->getResourceByPagetitle($row[0])) {
        $database->createResourceSkeleton($resource);
        foreach ($header as $index => $col) {
            if (is_numeric($col)) {
                if ($param = $database->getTemplateById($resource['id'], $col)) {
                    if (!empty($row[$index])) {
                        $database->updateTemplateById($param['id'], $row[$index]);
                    }
                } else {
                    $database->createTemplateByContentId($resource['id'], $col, $row[$index]);
                }
            }
        }
    } else {
        if ($_ENV['ONLY_UPDATE']) {
            continue;
        }

        if ($resource = $database->createResourceBySkeleton($row[0])) {
            foreach ($header as $index => $col) {
                if (is_numeric($col)) {
                    $database->createTemplateByContentId($resource['id'], $col, $row[$index]);
                }
            }
        }
    }
}

fclose($f);


