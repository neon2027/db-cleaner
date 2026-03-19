<?php

namespace Laravelldone\DbCleaner\Analyzers;

use Laravelldone\DbCleaner\DTOs\ColumnAnalysis;
use Laravelldone\DbCleaner\DTOs\TableAnalysis;
use Laravelldone\DbCleaner\Scoring\QualityScorer;
use Laravelldone\DbCleaner\Support\DatabaseIntrospector;

class AnalyzerPipeline
{
    protected array $analyzers;

    public function __construct(
        protected array $config = []
    ) {
        $this->analyzers = [
            new DuplicateAnalyzer($config),
            new WhitespaceAnalyzer($config),
            new CasingAnalyzer($config),
            new TypoAnalyzer($config),
        ];
    }

    public function analyze(string $table, array $columns = []): TableAnalysis
    {
        $introspector = new DatabaseIntrospector($this->config);
        $connection = $this->config['connection'] ?? config('database.default');
        $totalRows = $introspector->getRowCount($table);

        if (empty($columns)) {
            $columns = $introspector->getColumnsForTable($table);
        }

        $columnAnalyses = [];
        $scorer = new QualityScorer($this->config);

        foreach ($columns as $column) {
            $issues = [];

            foreach ($this->analyzers as $analyzer) {
                if (! $analyzer->isEnabled()) {
                    continue;
                }

                try {
                    $found = $analyzer->analyze($connection, $table, $column, $totalRows);
                    $issues = array_merge($issues, $found);
                } catch (\Throwable $e) {
                    // Skip analyzer errors on individual columns gracefully
                }
            }

            $dataType = $this->resolveDataType($introspector, $table, $column);
            $nullCount = $this->countNulls($connection, $table, $column);

            $columnAnalysis = new ColumnAnalysis(
                table: $table,
                column: $column,
                dataType: $dataType,
                totalRows: $totalRows,
                nullCount: $nullCount,
                issues: $issues,
            );

            $columnScore = $scorer->scoreColumn($columnAnalysis);
            $columnAnalyses[] = new ColumnAnalysis(
                table: $columnAnalysis->table,
                column: $columnAnalysis->column,
                dataType: $columnAnalysis->dataType,
                totalRows: $columnAnalysis->totalRows,
                nullCount: $columnAnalysis->nullCount,
                issues: $columnAnalysis->issues,
                qualityScore: $columnScore,
                grade: $scorer->grade($columnScore),
            );
        }

        $tableScore = empty($columnAnalyses)
            ? 100.0
            : array_sum(array_map(fn ($c) => $c->qualityScore, $columnAnalyses)) / count($columnAnalyses);

        return new TableAnalysis(
            table: $table,
            totalRows: $totalRows,
            columns: $columnAnalyses,
            qualityScore: round($tableScore, 2),
            grade: $scorer->grade($tableScore),
        );
    }

    protected function resolveDataType(DatabaseIntrospector $introspector, string $table, string $column): string
    {
        $connection = $this->config['connection'] ?? config('database.default');

        try {
            $cols = \Illuminate\Support\Facades\Schema::connection($connection)->getColumns($table);
            foreach ($cols as $col) {
                if ($col['name'] === $column) {
                    return $col['type_name'] ?? $col['type'] ?? 'varchar';
                }
            }
        } catch (\Throwable) {
        }

        return 'varchar';
    }

    protected function countNulls(string $connection, string $table, string $column): int
    {
        return (int) \Illuminate\Support\Facades\DB::connection($connection)
            ->table($table)
            ->whereNull($column)
            ->count();
    }
}
