<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;

class CsvImportService
{
    /**
     * Parse CSV uploaded file into key-value array rows.
     *
     * @param UploadedFile $file
     * @return array<int, array<string, mixed>>
     */
    public function parse(UploadedFile $file): array
    {
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            if ($header) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($header) === count($data)) {
                        $rows[] = array_combine($header, $data);
                    }
                }
            }
            fclose($handle);
        }

        return $rows;
    }
}
