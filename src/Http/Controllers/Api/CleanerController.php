<?php

namespace Laravelldone\DbCleaner\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravelldone\DbCleaner\Cleaners\CleanerPipeline;
use Laravelldone\DbCleaner\Events\CleaningApplied;

class CleanerController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'table' => 'required|string',
            'column' => 'required|string',
            'type' => 'required|string|in:whitespace,casing,duplicate',
        ]);

        $pipeline = new CleanerPipeline(config('db-cleaner', []));

        try {
            $actions = $pipeline->preview(
                $request->input('table'),
                $request->input('column'),
                $request->input('type'),
            );

            return response()->json([
                'preview' => $actions,
                'total_actions' => count($actions),
                'total_affected_rows' => array_sum(array_column($actions, 'affected_rows')),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'table' => 'required|string',
            'column' => 'required|string',
            'type' => 'required|string|in:whitespace,casing,duplicate',
            'confirm' => 'required|boolean|accepted',
        ]);

        $pipeline = new CleanerPipeline(config('db-cleaner', []));

        try {
            $actions = $pipeline->clean(
                $request->input('table'),
                $request->input('column'),
                $request->input('type'),
                confirm: true,
            );

            CleaningApplied::dispatch(
                $request->input('table'),
                $request->input('column'),
                $request->input('type'),
                $actions,
            );

            return response()->json([
                'message' => 'Cleaning applied successfully',
                'actions' => $actions,
                'total_affected_rows' => array_sum(array_column($actions, 'affected_rows')),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
