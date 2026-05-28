<?php

namespace App\Services;

/**
 * Tiny value object so import controllers/jobs can return a consistent
 * shape: total rows seen, rows imported, and a list of per-row errors.
 */
class CsvImportResult
{
    /** @var array<int, array{row: int, error: string}> */
    public array $errors = [];

    public int $imported = 0;

    public int $total = 0;

    public function fail(int $row, string $message): void
    {
        $this->errors[] = ['row' => $row, 'error' => $message];
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
