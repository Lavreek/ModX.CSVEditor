<?php

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . "/vendor/autoload.php";

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(ROOT_PATH . '/.env');

require_once ROOT_PATH . "/src/Database.php";
require_once ROOT_PATH . "/src/CSVFileCommander.php";

$database = new Database($_ENV['HOST'], $_ENV['USER'], $_ENV['PASSWORD'], $_ENV['DATABASE']);

$f = fopen(__DIR__ . "/" . $_ENV['FILE_NAME'], 'r+');

$csvCommander = new CSVFileCommander($f);

['header' => $header, 'rows' => $rows] = $csvCommander->getFileData();


foreach ($rows as $row) {
    if ($resource = $database->getResourcesByPagetitle($row[0])) {
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


