<?php

namespace Laravelldone\DbCleaner\Commands;

use Illuminate\Console\Command;
use Laravelldone\DbCleaner\Models\ScanResult;

class ReportCommand extends Command
{
    protected $signature = 'db-cleaner:report
                            {--table= : Show report for a specific table}
                            {--format=table : Output format: table, json, csv}
                            {--limit=10 : Number of recent scans to include}';

    protected $description = 'Display data quality reports from previous scans';

    public function handle(): int
    {
        $table = $this->option('table');
        $format = $this->option('format');
        $limit = (int) $this->option('limit');

        $query = ScanResult::query()
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($table) {
            $query->where('table_name', $table);
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            $this->warn('No scan results found. Run `php artisan db-cleaner:scan` first.');

            return self::SUCCESS;
        }

        $data = $results->map(fn ($r) => [
            'table' => $r->table_name,
            'score' => number_format($r->quality_score, 1),
            'grade' => $r->grade,
            'issues' => $r->total_issues,
            'rows' => $r->total_rows,
            'scanned_at' => $r->created_at->format('Y-m-d H:i'),
        ]);

        match ($format) {
            'json' => $this->line($results->toJson(JSON_PRETTY_PRINT)),
            'csv' => $this->outputCsv($data->all()),
            default => $this->table(
                ['Table', 'Score', 'Grade', 'Issues', 'Rows', 'Scanned At'],
                $data->map(fn ($r) => array_values($r))->all()
            ),
        };

        return self::SUCCESS;
    }

    protected function outputCsv(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $this->line(implode(',', array_keys($rows[0])));

        foreach ($rows as $row) {
            $this->line(implode(',', array_map(fn ($v) => '"'.$v.'"', array_values($row))));
        }
    }
}
