<?php

namespace Laravelldone\DbCleaner\DTOs;

class TableAnalysis
{
    /** @param ColumnAnalysis[] $columns */
    public function __construct(
        public readonly string $table,
        public readonly int $totalRows,
        public readonly array $columns = [],
        public readonly float $qualityScore = 100.0,
        public readonly string $grade = 'A',
        public readonly \DateTimeImmutable $scannedAt = new \DateTimeImmutable(),
    ) {}

    public function totalIssueCount(): int
    {
        return array_sum(array_map(fn ($c) => $c->totalIssueCount(), $this->columns));
    }

    public function issuesByType(string $type): array
    {
        $issues = [];
        foreach ($this->columns as $column) {
            foreach ($column->issuesByType($type) as $issue) {
                $issues[] = $issue;
            }
        }

        return $issues;
    }

    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'total_rows' => $this->totalRows,
            'quality_score' => $this->qualityScore,
            'grade' => $this->grade,
            'total_issues' => $this->totalIssueCount(),
            'scanned_at' => $this->scannedAt->format('Y-m-d H:i:s'),
            'columns' => array_map(fn ($c) => $c->toArray(), $this->columns),
        ];
    }
}
