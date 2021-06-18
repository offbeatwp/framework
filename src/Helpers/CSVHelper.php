<?php

namespace OffbeatWP\Helpers;

class CSVHelper
{
    /**
     * @param string[][] $data
     * @param string $filename
     * @param string $separator
     */
    public static function arrayToCsvDownload(array $data, string $filename = "export.csv", string $separator = ","): void
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $stream = fopen('php://output', 'w');

        foreach ($data as $fields) {
            fputcsv($stream, $fields, $separator);
        }
    }
}