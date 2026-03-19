<?php

namespace Laravelldone\DbCleaner\DTOs;

class ColumnAnalysis
{
    /** @param Issue[] $issues */
    public function __construct(
        public readonly string $table,
        public readonly string $column,
        public readonly string $dataType,
        public readonly int $totalRows,
        public readonly int $nullCount,
        public readonly array $issues = [],
        public readonly float $qualityScore = 100.0,
        public readonly string $grade = 'A',
    ) {}

    public function issuesByType(string $type): array
    {
        return array_values(array_filter($this->issues, fn ($i) => $i->type === $type));
    }

    public function totalIssueCount(): int
    {
        return array_sum(array_map(fn ($i) => $i->count, $this->issues));
    }

    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'column' => $this->column,
            'data_type' => $this->dataType,
            'total_rows' => $this->totalRows,
            'null_count' => $this->nullCount,
            'quality_score' => $this->qualityScore,
            'grade' => $this->grade,
            'total_issues' => $this->totalIssueCount(),
            'issues' => array_map(fn ($i) => $i->toArray(), $this->issues),
        ];
    }
}
