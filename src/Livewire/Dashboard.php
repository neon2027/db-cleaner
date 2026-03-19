<?php

namespace Laravelldone\DbCleaner\Livewire;

use Laravelldone\DbCleaner\Models\ScanResult;
use Laravelldone\DbCleaner\Support\DatabaseIntrospector;
use Livewire\Component;

class Dashboard extends Component
{
    public array $tables = [];

    public float $averageScore = 0;

    public string $overallGrade = 'N/A';

    public int $totalIssues = 0;

    public array $gradeDistribution = [];

    public array $issueBreakdown = [];

    public array $scoreHistory = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $config = config('db-cleaner', []);
        $introspector = new DatabaseIntrospector($config);
        $tableNames = $introspector->getTablesToScan();

        $latestScans = ScanResult::query()
            ->whereIn('table_name', $tableNames)
            ->whereNull('column_name')
            ->orderByDesc('created_at')
            ->get()
            ->unique('table_name');

        $this->tables = $latestScans->map(fn ($r) => [
            'name' => $r->table_name,
            'score' => $r->quality_score,
            'grade' => $r->grade,
            'issues' => $r->total_issues,
            'scanned_at' => $r->created_at?->diffForHumans(),
        ])->sortBy('score')->values()->all();

        $this->averageScore = $latestScans->isNotEmpty()
            ? round($latestScans->avg('quality_score'), 1)
            : 0;

        $this->totalIssues = (int) $latestScans->sum('total_issues');

        $this->overallGrade = $this->gradeFromScore($this->averageScore);

        $this->gradeDistribution = $latestScans
            ->groupBy('grade')
            ->map->count()
            ->toArray();

        $this->issueBreakdown = [
            'duplicates' => 0,
            'whitespace' => 0,
            'casing' => 0,
            'typos' => 0,
        ];

        foreach ($latestScans as $scan) {
            $breakdown = $scan->issue_breakdown ?? [];
            foreach (array_keys($this->issueBreakdown) as $key) {
                $this->issueBreakdown[$key] += $breakdown[$key] ?? 0;
            }
        }

        $this->scoreHistory = ScanResult::query()
            ->whereNull('column_name')
            ->selectRaw('DATE(created_at) as date, AVG(quality_score) as avg_score')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->limit(30)
            ->get()
            ->map(fn ($r) => ['date' => $r->date, 'score' => round($r->avg_score, 1)])
            ->all();
    }

    public function refresh(): void
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }

    protected function gradeFromScore(float $score): string
    {
        return match (true) {
            $score >= 95 => 'A',
            $score >= 85 => 'B',
            $score >= 70 => 'C',
            $score >= 50 => 'D',
            $score > 0 => 'F',
            default => 'N/A',
        };
    }

    public function render()
    {
        return view('db-cleaner::livewire.dashboard');
    }
}
