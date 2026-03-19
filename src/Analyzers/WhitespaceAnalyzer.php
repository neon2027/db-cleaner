<?php

namespace Laravelldone\DbCleaner\Analyzers;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\AnalyzerContract;
use Laravelldone\DbCleaner\DTOs\Issue;

class WhitespaceAnalyzer implements AnalyzerContract
{
    public function __construct(
        protected array $config = []
    ) {}

    public function isEnabled(): bool
    {
        return (bool) ($this->config['whitespace']['enabled'] ?? true);
    }

    /** @return Issue[] */
    public function analyze(string $connection, string $table, string $column, int $totalRows): array
    {
        $issues = [];

        if ($this->config['whitespace']['leading'] ?? true) {
            $count = $this->countLeading($connection, $table, $column);
            if ($count > 0) {
                $issues[] = new Issue(
                    type: 'whitespace',
                    subtype: 'leading',
                    value: null,
                    suggestion: 'LTRIM',
                    count: $count,
                );
            }
        }

        if ($this->config['whitespace']['trailing'] ?? true) {
            $count = $this->countTrailing($connection, $table, $column);
            if ($count > 0) {
                $issues[] = new Issue(
                    type: 'whitespace',
                    subtype: 'trailing',
                    value: null,
                    suggestion: 'RTRIM',
                    count: $count,
                );
            }
        }

        if ($this->config['whitespace']['double_spaces'] ?? true) {
            $count = $this->countDoubleSpaces($connection, $table, $column);
            if ($count > 0) {
                $issues[] = new Issue(
                    type: 'whitespace',
                    subtype: 'double_spaces',
                    value: null,
                    suggestion: 'Replace double spaces',
                    count: $count,
                );
            }
        }

        if ($this->config['whitespace']['tabs'] ?? true) {
            $count = $this->countTabs($connection, $table, $column);
            if ($count > 0) {
                $issues[] = new Issue(
                    type: 'whitespace',
                    subtype: 'tabs',
                    value: null,
                    suggestion: 'Replace tabs with spaces',
                    count: $count,
                );
            }
        }

        return $issues;
    }

    protected function countLeading(string $connection, string $table, string $column): int
    {
        return (int) DB::connection($connection)
            ->table($table)
            ->whereNotNull($column)
            ->whereRaw("{$column} != LTRIM({$column})")
            ->count();
    }

    protected function countTrailing(string $connection, string $table, string $column): int
    {
        return (int) DB::connection($connection)
            ->table($table)
            ->whereNotNull($column)
            ->whereRaw("{$column} != RTRIM({$column})")
            ->count();
    }

    protected function countDoubleSpaces(string $connection, string $table, string $column): int
    {
        return (int) DB::connection($connection)
            ->table($table)
            ->whereNotNull($column)
            ->where($column, 'like', '%  %')
            ->count();
    }

    protected function countTabs(string $connection, string $table, string $column): int
    {
        return (int) DB::connection($connection)
            ->table($table)
            ->whereNotNull($column)
            ->where($column, 'like', "%\t%")
            ->count();
    }
}
