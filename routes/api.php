<?php

use Illuminate\Support\Facades\Route;
use Laravelldone\DbCleaner\Http\Controllers\Api\AnalysisController;
use Laravelldone\DbCleaner\Http\Controllers\Api\CleanerController;
use Laravelldone\DbCleaner\Http\Controllers\Api\StatusController;
use Laravelldone\DbCleaner\Http\Middleware\DbCleanerApiAuth;

$prefix = config('db-cleaner.api.prefix', 'api/db-cleaner');
$middleware = array_merge(
    config('db-cleaner.api.middleware', ['api']),
    [DbCleanerApiAuth::class]
);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        Route::get('status', StatusController::class);

        Route::get('tables', [AnalysisController::class, 'index']);
        Route::get('tables/{table}', [AnalysisController::class, 'show']);
        Route::post('tables/{table}/scan', [AnalysisController::class, 'scan']);
        Route::get('history', [AnalysisController::class, 'history']);

        Route::post('clean/preview', [CleanerController::class, 'preview']);
        Route::post('clean/apply', [CleanerController::class, 'apply']);
    });
