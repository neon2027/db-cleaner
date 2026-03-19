<?php

namespace Laravelldone\DbCleaner\Analyzers;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\AnalyzerContract;
use Laravelldone\DbCleaner\DTOs\Issue;
use Laravelldone\DbCleaner\Support\FuzzyMatcher;

class TypoAnalyzer implements AnalyzerContract
{
    public function __construct(
        protected array $config = []
    ) {}

    public function isEnabled(): bool
    {
        return (bool) ($this->config['typos']['enabled'] ?? true);
    }

    /** @return Issue[] */
    public function analyze(string $connection, string $table, string $column, int $totalRows): array
    {
        $maxRows = (int) ($this->config['typos']['max_rows_for_typos'] ?? 5000);

        if ($totalRows > $maxRows) {
            return [];
        }

        $threshold = (int) ($this->config['typos']['similarity_threshold'] ?? 85);
        $minFrequency = (int) ($this->config['typos']['min_frequency'] ?? 2);

        $valueCounts = DB::connection($connection)
            ->table($table)
            ->select([$column, DB::raw('COUNT(*) as cnt')])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->pluck('cnt', $column)
            ->all();

        if (count($valueCounts) < 2) {
            return [];
        }

        $pairs = FuzzyMatcher::findTypoLikePairs($valueCounts, $minFrequency, $threshold);

        $issues = [];

        foreach ($pairs as $rareVal => $canonical) {
            $count = (int) ($valueCounts[$rareVal] ?? 0);

            $issues[] = new Issue(
                type: 'typo',
                subtype: 'similar_text',
                value: $rareVal,
                suggestion: $canonical,
                count: $count,
                meta: [
                    'canonical' => $canonical,
                    'canonical_count' => (int) ($valueCounts[$canonical] ?? 0),
                ],
            );
        }

        return $issues;
    }
}
