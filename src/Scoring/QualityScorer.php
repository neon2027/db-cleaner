<?php

namespace Laravelldone\DbCleaner\Scoring;

use Laravelldone\DbCleaner\Contracts\ScorerContract;
use Laravelldone\DbCleaner\DTOs\ColumnAnalysis;
use Laravelldone\DbCleaner\DTOs\TableAnalysis;

class QualityScorer implements ScorerContract
{
    protected array $weights;

    public function __construct(
        protected array $config = []
    ) {
        $rawWeights = $config['scoring_weights'] ?? [
            'duplicates' => 30,
            'whitespace' => 20,
            'casing' => 25,
            'typos' => 25,
        ];

        $total = array_sum($rawWeights);
        $this->weights = array_map(fn ($w) => $w / $total, $rawWeights);
    }

    public function score(TableAnalysis $analysis): ScoreReport
    {
        $columnReports = [];
        foreach ($analysis->columns as $column) {
            // AnalyzerPipeline pre-bakes qualityScore; use it directly.
            // Falls back to scoreColumn() only when the column hasn't been scored yet
            // (i.e., default 100.0 but has issues recorded).
            $columnReports[$column->column] = (! empty($column->issues) && $column->qualityScore === 100.0)
                ? $this->scoreColumn($column)
                : $column->qualityScore;
        }

        $overall = empty($columnReports)
            ? 100.0
            : array_sum($columnReports) / count($columnReports);

        return new ScoreReport(
            table: $analysis->table,
            overallScore: round($overall, 2),
            grade: $this->grade($overall),
            columnScores: $columnReports,
            issueBreakdown: $this->buildIssueBreakdown($analysis),
        );
    }

    public function scoreColumn(ColumnAnalysis $column): float
    {
        if ($column->totalRows === 0) {
            return 100.0;
        }

        $penalties = [];

        foreach (['duplicate', 'whitespace', 'casing', 'typo'] as $type) {
            $weightKey = $type === 'duplicate' ? 'duplicates' : ($type === 'typo' ? 'typos' : $type);
            $weight = $this->weights[$weightKey] ?? 0.25;

            $issuesOfType = $column->issuesByType($type);
            $affectedRows = array_sum(array_map(fn ($i) => $i->count, $issuesOfType));

            // What fraction of rows are affected by this issue type?
            $fraction = min(1.0, $affectedRows / $column->totalRows);

            // Penalty for this type: weight * fraction * 100
            $penalties[$type] = $weight * $fraction * 100;
        }

        $totalPenalty = min(100.0, array_sum($penalties));

        return max(0.0, round(100.0 - $totalPenalty, 2));
    }

    public function grade(float $score): string
    {
        return match (true) {
            $score >= 95 => 'A',
            $score >= 85 => 'B',
            $score >= 70 => 'C',
            $score >= 50 => 'D',
            default => 'F',
        };
    }

    protected function buildIssueBreakdown(TableAnalysis $analysis): array
    {
        $breakdown = [
            'duplicates' => 0,
            'whitespace' => 0,
            'casing' => 0,
            'typos' => 0,
        ];

        foreach ($analysis->columns as $column) {
            foreach ($column->issues as $issue) {
                $key = match ($issue->type) {
                    'duplicate' => 'duplicates',
                    'whitespace' => 'whitespace',
                    'casing' => 'casing',
                    'typo' => 'typos',
                    default => null,
                };

                if ($key !== null) {
                    $breakdown[$key] += $issue->count;
                }
            }
        }

        return $breakdown;
    }
}
