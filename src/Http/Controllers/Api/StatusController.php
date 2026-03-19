<?php

namespace Laravelldone\DbCleaner\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravelldone\DbCleaner\Models\ScanResult;
use Laravelldone\DbCleaner\Support\DatabaseIntrospector;

class StatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $config = config('db-cleaner', []);
        $introspector = new DatabaseIntrospector($config);
        $tables = $introspector->getTablesToScan();

        $latestScans = ScanResult::query()
            ->whereIn('table_name', $tables)
            ->whereNull('column_name')
            ->orderByDesc('created_at')
            ->get()
            ->unique('table_name');

        $scanned = $latestScans->count();
        $totalTables = count($tables);
        $avgScore = $scanned > 0 ? round($latestScans->avg('quality_score'), 2) : null;
        $totalIssues = $latestScans->sum('total_issues');

        $gradeDistribution = $latestScans
            ->groupBy('grade')
            ->map->count()
            ->toArray();

        return response()->json([
            'status' => 'ok',
            'summary' => [
                'total_tables' => $totalTables,
                'scanned_tables' => $scanned,
                'unscanned_tables' => $totalTables - $scanned,
                'average_quality_score' => $avgScore,
                'total_issues' => $totalIssues,
                'grade_distribution' => $gradeDistribution,
            ],
            'tables' => $latestScans->map(fn ($r) => [
                'table' => $r->table_name,
                'score' => $r->quality_score,
                'grade' => $r->grade,
                'issues' => $r->total_issues,
                'scanned_at' => $r->created_at,
            ])->values(),
        ]);
    }
}
