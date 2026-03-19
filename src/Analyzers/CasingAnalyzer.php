<?php

namespace Laravelldone\DbCleaner\Analyzers;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\AnalyzerContract;
use Laravelldone\DbCleaner\DTOs\Issue;

class CasingAnalyzer implements AnalyzerContract
{
    public function __construct(
        protected array $config = []
    ) {}

    public function isEnabled(): bool
    {
        return (bool) ($this->config['casing']['enabled'] ?? true);
    }

    /** @return Issue[] */
    public function analyze(string $connection, string $table, string $column, int $totalRows): array
    {
        $groups = $this->findCasingGroups($connection, $table, $column);

        if (empty($groups)) {
            return [];
        }

        $issues = [];

        foreach ($groups as $normalizedValue => $variants) {
            $counts = [];
            foreach ($variants as $variant) {
                $counts[$variant] = DB::connection($connection)
                    ->table($table)
                    ->where($column, $variant)
                    ->count();
            }

            arsort($counts);
            $canonical = array_key_first($counts);

            foreach ($variants as $variant) {
                if ($variant === $canonical) {
                    continue;
                }

                $issues[] = new Issue(
                    type: 'casing',
                    subtype: 'inconsistent',
                    value: $variant,
                    suggestion: $canonical,
                    count: (int) ($counts[$variant] ?? 0),
                    meta: ['canonical' => $canonical, 'normalized' => $normalizedValue],
                );
            }
        }

        return $issues;
    }

    /**
     * Returns [lowercased_value => [variant1, variant2, ...]] for groups with > 1 casing variant.
     */
    protected function findCasingGroups(string $connection, string $table, string $column): array
    {
        $driver = DB::connection($connection)->getDriverName();

        // Group by LOWER(column) and find groups with more than 1 distinct variant
        $rows = DB::connection($connection)
            ->table($table)
            ->selectRaw("LOWER({$column}) as normalized, COUNT(DISTINCT {$column}) as variant_count")
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupByRaw("LOWER({$column})")
            ->havingRaw('COUNT(DISTINCT '.$column.') > 1')
            ->limit(50)
            ->get();

        $groups = [];

        foreach ($rows as $row) {
            $normalized = $row->normalized;

            $variants = DB::connection($connection)
                ->table($table)
                ->selectRaw("DISTINCT {$column}")
                ->whereRaw("LOWER({$column}) = ?", [strtolower($normalized)])
                ->pluck($column)
                ->all();

            if (count($variants) > 1) {
                $groups[$normalized] = $variants;
            }
        }

        return $groups;
    }
}
