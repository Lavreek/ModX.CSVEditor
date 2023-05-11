<?php

class CSVFileCommander
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getFileData()
    {
        $header = [];
        $rows = [];

        while ($row = fgetcsv(stream: $this->file, separator: ';')) {
            if (!$header) {
                $header = $row;
            } else {
                array_push($rows, $row);
            }
        }

        return ['header' => $header, 'rows' => $rows];
    }
}