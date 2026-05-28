<?php

namespace App\Services;

/**
 * Small helpers for CSV import/export. We avoid pulling in a heavy package
 * (league/csv, maatwebsite/excel) because the imports are intentionally
 * narrow — just the seven kinds the audit calls for.
 */
class CsvIo
{
    /**
     * Stream rows out of an uploaded CSV file. Header row is detected,
     * trimmed, and lower-snake-cased so callers can reference fields like
     * "first_name" regardless of how the original column was capitalised.
     *
     * @return \Generator<int, array<string, string>>
     */
    public static function read(string $path): \Generator
    {
        $fh = fopen($path, 'rb');
        if (! $fh) {
            return;
        }

        $headers = null;
        try {
            while (($row = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
                if ($headers === null) {
                    $headers = array_map(fn ($h) => self::normaliseKey((string) $h), $row);

                    continue;
                }

                $assoc = [];
                foreach ($headers as $i => $key) {
                    $assoc[$key] = trim((string) ($row[$i] ?? ''));
                }
                yield $assoc;
            }
        } finally {
            fclose($fh);
        }
    }

    /**
     * Render a 2-D dataset as a streamed download. Closures keep the data
     * lazy so large exports stay memory-stable.
     *
     * @param  array<int, string>  $headers
     * @param  \Closure  $rows  yields array<int, string|int|float|null>
     */
    public static function download(string $filename, array $headers, \Closure $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, $headers, ',', '"', '\\');
            foreach ($rows() as $row) {
                fputcsv($out, array_values($row), ',', '"', '\\');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private static function normaliseKey(string $header): string
    {
        $h = strtolower(trim($header));
        $h = preg_replace('/[^a-z0-9]+/', '_', $h) ?? $h;

        return trim($h, '_');
    }
}
