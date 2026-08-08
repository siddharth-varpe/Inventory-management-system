<?php

declare(strict_types=1);

namespace App\Services\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * Download collection/array as CSV streamed response.
     *
     * @param string $filename
     * @param array<int, string> $headers
     * @param iterable $data
     * @return StreamedResponse
     */
    public function download(string $filename, array $headers, iterable $data): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->stream(function () use ($headers, $data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($data as $row) {
                fputcsv($handle, (array) $row);
            }

            fclose($handle);
        }, 200, $responseHeaders);
    }
}
