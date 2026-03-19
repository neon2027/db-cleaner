<?php

namespace Laravelldone\DbCleaner\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravelldone\DbCleaner\DbCleaner;
use Laravelldone\DbCleaner\Events\ScanCompleted;
use Laravelldone\DbCleaner\Models\ScanResult;
use Laravelldone\DbCleaner\Scoring\QualityScorer;
use Laravelldone\DbCleaner\Support\DatabaseIntrospector;

class AnalysisController extends Controller
{
    public function __construct(
        protected DbCleaner $cleaner
    ) {}

    public function index(): JsonResponse
    {
        $config = config('db-cleaner', []);
        $introspector = new DatabaseIntrospector($config);
        $tables = $introspector->getTablesToScan();

        $latestScans = ScanResult::query()
            ->whereIn('table_name', $tables)
            ->whereNull('column_name')
            ->orderByDesc('created_at')
            ->get()
            ->unique('table_name')
            ->keyBy('table_name');

        return response()->json([
            'tables' => array_map(fn ($table) => [
                'table' => $table,
                'score' => $latestScans[$table]?->quality_score,
                'grade' => $latestScans[$table]?->grade,
                'issues' => $latestScans[$table]?->total_issues,
                'scanned_at' => $latestScans[$table]?->created_at,
            ], $tables),
        ]);
    }

    public function show(string $table): JsonResponse
    {
        $result = ScanResult::query()
            ->where('table_name', $table)
            ->whereNull('column_name')
            ->latest()
            ->first();

        if (! $result) {
            return response()->json(['error' => 'No scan results for this table. Run a scan first.'], 404);
        }

        return response()->json($result->raw_analysis);
    }

    public function scan(Request $request, string $table): JsonResponse
    {
        $columns = $request->input('columns', []);

        try {
            $analysis = $this->cleaner->scan($table, $columns);
            $scorer = new QualityScorer(config('db-cleaner', []));
            $report = $scorer->score($analysis);

            $scanResult = ScanResult::fromAnalysis($analysis, $report, config('db-cleaner.connection') ?? config('database.default'));
            ScanCompleted::dispatch($analysis, $scanResult);

            return response()->json([
                'message' => 'Scan completed',
                'result' => $analysis->toArray(),
                'score' => $report->toArray(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        $table = $request->input('table');
        $limit = (int) $request->input('limit', 30);

        $query = ScanResult::query()
            ->whereNull('column_name')
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($table) {
            $query->where('table_name', $table);
        }

        return response()->json([
            'history' => $query->get()->map(fn ($r) => [
                'table' => $r->table_name,
                'score' => $r->quality_score,
                'grade' => $r->grade,
                'issues' => $r->total_issues,
                'issue_breakdown' => $r->issue_breakdown,
                'scanned_at' => $r->created_at,
            ]),
        ]);
    }
}
