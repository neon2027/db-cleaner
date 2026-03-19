<?php

namespace Laravelldone\DbCleaner\Scoring;

class ScoreReport
{
    public function __construct(
        public readonly string $table,
        public readonly float $overallScore,
        public readonly string $grade,
        public readonly array $columnScores = [],   // column => score
        public readonly array $issueBreakdown = [], // type => count
    ) {}

    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'overall_score' => $this->overallScore,
            'grade' => $this->grade,
            'column_scores' => $this->columnScores,
            'issue_breakdown' => $this->issueBreakdown,
        ];
    }
}
