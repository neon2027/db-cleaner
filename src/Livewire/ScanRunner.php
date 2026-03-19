<?php

namespace Laravelldone\DbCleaner\Livewire;

use Laravelldone\DbCleaner\DbCleaner;
use Laravelldone\DbCleaner\Events\ScanCompleted;
use Laravelldone\DbCleaner\Models\ScanResult;
use Laravelldone\DbCleaner\Scoring\QualityScorer;
use Livewire\Component;

class ScanRunner extends Component
{
    public string $table = '';

    public bool $scanning = false;

    public ?string $lastError = null;

    public ?array $lastResult = null;

    public function scan(): void
    {
        if (! $this->table) {
            $this->lastError = 'Please select a table.';

            return;
        }

        $this->scanning = true;
        $this->lastError = null;
        $this->lastResult = null;

        try {
            $cleaner = app(DbCleaner::class);
            $analysis = $cleaner->scan($this->table);
            $scorer = new QualityScorer(config('db-cleaner', []));
            $report = $scorer->score($analysis);

            $scanResult = ScanResult::fromAnalysis($analysis, $report, config('db-cleaner.connection') ?? config('database.default'));
            ScanCompleted::dispatch($analysis, $scanResult);

            $this->lastResult = [
                'score' => $report->overallScore,
                'grade' => $report->grade,
                'issues' => $analysis->totalIssueCount(),
                'table' => $this->table,
            ];

            $this->dispatch('scan-completed', table: $this->table);
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->scanning = false;
        }
    }

    public function render()
    {
        return view('db-cleaner::livewire.scan-runner');
    }
}
