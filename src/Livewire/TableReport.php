<?php

namespace Laravelldone\DbCleaner\Livewire;

use Laravelldone\DbCleaner\Models\ScanResult;
use Livewire\Component;

class TableReport extends Component
{
    public string $table = '';

    public ?array $analysis = null;

    public array $history = [];

    public function mount(string $table): void
    {
        $this->table = $table;
        $this->loadData();
    }

    public function loadData(): void
    {
        $result = ScanResult::query()
            ->where('table_name', $this->table)
            ->whereNull('column_name')
            ->latest()
            ->first();

        $this->analysis = $result?->raw_analysis;

        $this->history = ScanResult::query()
            ->where('table_name', $this->table)
            ->whereNull('column_name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'score' => $r->quality_score,
                'grade' => $r->grade,
                'issues' => $r->total_issues,
                'scanned_at' => $r->created_at->format('Y-m-d H:i'),
            ])
            ->all();
    }

    public function render()
    {
        return view('db-cleaner::livewire.table-report');
    }
}
