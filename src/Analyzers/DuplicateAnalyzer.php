<?php

namespace Laravelldone\DbCleaner\Analyzers;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\AnalyzerContract;
use Laravelldone\DbCleaner\DTOs\Issue;
use Laravelldone\DbCleaner\Support\FuzzyMatcher;

class DuplicateAnalyzer implements AnalyzerContract
{
    public function __construct(
        protected array $config = []
    ) {}

    public function isEnabled(): bool
    {
        return (bool) ($this->config['duplicates']['enabled'] ?? true);
    }

    /** @return Issue[] */
    public function analyze(string $connection, string $table, string $column, int $totalRows): array
    {
        $issues = [];

        if ($this->config['duplicates']['exact'] ?? true) {
            $issues = array_merge($issues, $this->detectExact($connection, $table, $column));
        }

        $maxRows = (int) ($this->config['duplicates']['max_rows_for_fuzzy'] ?? 5000);

        if ($totalRows <= $maxRows) {
            if ($this->config['duplicates']['fuzzy'] ?? true) {
                $issues = array_merge($issues, $this->detectFuzzy($connection, $table, $column));
            }

            if ($this->config['duplicates']['soundex'] ?? true) {
                $issues = array_merge($issues, $this->detectSoundex($connection, $table, $column));
            }
        }

        return $issues;
    }

    protected function detectExact(string $connection, string $table, string $column): array
    {
        $rows = DB::connection($connection)
            ->table($table)
            ->select([$column, DB::raw('COUNT(*) as cnt')])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('cnt')
            ->limit(100)
            ->get();

        return $rows->map(fn ($row) => new Issue(
            type: 'duplicate',
            subtype: 'exact',
            value: $row->{$column},
            suggestion: null,
            count: (int) $row->cnt,
        ))->all();
    }

    protected function detectFuzzy(string $connection, string $table, string $column): array
    {
        $threshold = (int) ($this->config['duplicates']['fuzzy_threshold'] ?? 2);

        $uniqueValues = DB::connection($connection)
            ->table($table)
            ->selectRaw("DISTINCT {$column}")
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column)
            ->all();

        if (count($uniqueValues) < 2) {
            return [];
        }

        $groups = FuzzyMatcher::groupByLevenshtein($uniqueValues, $threshold);

        $issues = [];
        foreach ($groups as $canonical => $similar) {
            foreach ($similar as $val) {
                if ($val === $canonical) {
                    continue;
                }

                $count = DB::connection($connection)
                    ->table($table)
                    ->where($column, $val)
                    ->count();

                $issues[] = new Issue(
                    type: 'duplicate',
                    subtype: 'fuzzy',
                    value: $val,
                    suggestion: $canonical,
                    count: $count,
                    meta: ['canonical' => $canonical, 'distance' => levenshtein(strtolower($val), strtolower($canonical))],
                );
            }
        }

        return $issues;
    }

    protected function detectSoundex(string $connection, string $table, string $column): array
    {
        $uniqueValues = DB::connection($connection)
            ->table($table)
            ->selectRaw("DISTINCT {$column}")
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column)
            ->all();

        if (count($uniqueValues) < 2) {
            return [];
        }

        $groups = FuzzyMatcher::groupBySoundex($uniqueValues);

        $issues = [];
        foreach ($groups as $code => $variants) {
            $counts = DB::connection($connection)
                ->table($table)
                ->select([$column, DB::raw('COUNT(*) as cnt')])
                ->whereIn($column, $variants)
                ->groupBy($column)
                ->pluck('cnt', $column)
                ->all();

            arsort($counts);
            $canonical = array_key_first($counts);

            foreach ($variants as $val) {
                if ($val === $canonical) {
                    continue;
                }

                $issues[] = new Issue(
                    type: 'duplicate',
                    subtype: 'soundex',
                    value: $val,
                    suggestion: $canonical,
                    count: (int) ($counts[$val] ?? 0),
                    meta: ['canonical' => $canonical, 'soundex_code' => $code],
                );
            }
        }

        return $issues;
    }
}
