<?php

namespace Laravelldone\DbCleaner\Commands;

use Illuminate\Console\Command;
use Laravelldone\DbCleaner\DbCleaner;
use Laravelldone\DbCleaner\Events\ScanCompleted;
use Laravelldone\DbCleaner\Models\ScanResult;
use Laravelldone\DbCleaner\Scoring\QualityScorer;

class ScanCommand extends Command
{
    protected $signature = 'db-cleaner:scan
                            {--table= : Scan a specific table}
                            {--columns= : Comma-separated columns to scan}
                            {--connection= : Database connection to use}';

    protected $description = 'Scan database tables for data quality issues';

    public function handle(DbCleaner $cleaner): int
    {
        $table = $this->option('table');
        $columnsOption = $this->option('columns');
        $connection = $this->option('connection');

        if ($connection) {
            config(['db-cleaner.connection' => $connection]);
        }

        $columns = $columnsOption ? explode(',', $columnsOption) : [];

        if ($table) {
            $this->scanTable($cleaner, $table, $columns);
        } else {
            $tables = $cleaner->getTables();

            if (empty($tables)) {
                $this->warn('No tables found to scan.');

                return self::SUCCESS;
            }

            $this->info('Scanning '.count($tables).' table(s)...');

            foreach ($tables as $t) {
                $this->scanTable($cleaner, $t, []);
            }
        }

        return self::SUCCESS;
    }

    protected function scanTable(DbCleaner $cleaner, string $table, array $columns): void
    {
        $this->line("Scanning <comment>{$table}</comment>...");

        try {
            $analysis = $cleaner->scan($table, $columns);
            $scorer = new QualityScorer(config('db-cleaner', []));
            $report = $scorer->score($analysis);

            $scanResult = ScanResult::fromAnalysis($analysis, $report, config('db-cleaner.connection', 'default'));

            ScanCompleted::dispatch($analysis, $scanResult);

            $this->table(
                ['Column', 'Score', 'Grade', 'Duplicates', 'Whitespace', 'Casing', 'Typos'],
                collect($analysis->columns)->map(fn ($col) => [
                    $col->column,
                    number_format($col->qualityScore, 1),
                    $col->grade,
                    count($col->issuesByType('duplicate')),
                    count($col->issuesByType('whitespace')),
                    count($col->issuesByType('casing')),
                    count($col->issuesByType('typo')),
                ])->all()
            );

            $gradeColor = match ($report->grade) {
                'A' => 'green',
                'B' => 'cyan',
                'C' => 'yellow',
                'D', 'F' => 'red',
                default => 'white',
            };

            $this->line("  Overall: <fg={$gradeColor}>{$report->grade}</> ({$report->overallScore}/100) — {$analysis->totalIssueCount()} total issues");
            $this->newLine();
        } catch (\Throwable $e) {
            $this->error("  Failed to scan {$table}: {$e->getMessage()}");
        }
    }
}
