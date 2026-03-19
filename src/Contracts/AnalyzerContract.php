<?php

namespace Laravelldone\DbCleaner\Contracts;

use Laravelldone\DbCleaner\DTOs\Issue;

interface AnalyzerContract
{
    /**
     * Analyze a specific column and return any issues found.
     *
     * @param  string  $connection  DB connection name
     * @param  string  $table  Table name
     * @param  string  $column  Column name
     * @param  int  $totalRows  Total rows in the table (for PHP-side guards)
     * @return Issue[]
     */
    public function analyze(string $connection, string $table, string $column, int $totalRows): array;

    /**
     * Whether this analyzer is enabled per config.
     */
    public function isEnabled(): bool;
}
