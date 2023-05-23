<?php

namespace Lavre\ModXCSV;

class FileCommander
{
    private string $file;

    const default_dirs = [ROOT_PATH . "/files", ROOT_PATH . "/results"];

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->makeDefaultDirs();
    }

    public function readFileData() : array
    {
        $header = [];
        $rows = [];

        $f = fopen($this->file, 'r+');

        while ($row = fgetcsv(stream: $f, separator: ';')) {
            if (!$header) {
                $header = $row;
            } else {
                array_push($rows, $row);
            }
        }

        return ['header' => $header, 'rows' => $rows];
    }

    public function writeFileData($data, $append = true, $newline = false) : void
    {
        $put = false;

        if ($newline) {
            $data = $data . "\n";
        }

        while ($put === false) {
            if ($append) {
                $put = file_put_contents($this->file, $data, FILE_APPEND);
            } else {
                $put = file_put_contents($this->file, $data);
            }
        }
    }

    private function makeDefaultDirs() : void
    {
        foreach (self::default_dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }
    }
}