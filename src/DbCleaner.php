<?php

namespace Laravelldone\DbCleaner;

use Laravelldone\DbCleaner\Analyzers\AnalyzerPipeline;
use Laravelldone\DbCleaner\Cleaners\CleanerPipeline;
use Laravelldone\DbCleaner\DTOs\TableAnalysis;
use Laravelldone\DbCleaner\Scoring\QualityScorer;
use Laravelldone\DbCleaner\Scoring\ScoreReport;
use Laravelldone\DbCleaner\Support\DatabaseIntrospector;

class DbCleaner
{
    public function __construct(
        protected array $config = []
    ) {}

    public function scan(string $table, array $columns = []): TableAnalysis
    {
        $pipeline = new AnalyzerPipeline($this->config);

        return $pipeline->analyze($table, $columns);
    }

    public function scanAll(): array
    {
        $introspector = new DatabaseIntrospector($this->config);
        $tables = $introspector->getTablesToScan();
        $results = [];

        foreach ($tables as $table) {
            $results[$table] = $this->scan($table);
        }

        return $results;
    }

    public function score(TableAnalysis $analysis): ScoreReport
    {
        $scorer = new QualityScorer($this->config);

        return $scorer->score($analysis);
    }

    public function clean(string $table, string $column, string $type, bool $confirm = false): array
    {
        $pipeline = new CleanerPipeline($this->config);

        return $pipeline->clean($table, $column, $type, $confirm);
    }

    public function previewClean(string $table, string $column, string $type): array
    {
        $pipeline = new CleanerPipeline($this->config);

        return $pipeline->preview($table, $column, $type);
    }

    public function getTables(): array
    {
        $introspector = new DatabaseIntrospector($this->config);

        return $introspector->getTablesToScan();
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
