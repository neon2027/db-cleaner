<?php

use Illuminate\Support\Facades\Route;

if (! config('db-cleaner.dashboard.enabled', true)) {
    return;
}

$prefix = config('db-cleaner.dashboard.prefix', 'db-cleaner');
$middleware = config('db-cleaner.dashboard.middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        Route::get('/', function () {
            return view('db-cleaner::livewire.dashboard');
        })->name('db-cleaner.dashboard');

        Route::get('/table/{table}', function (string $table) {
            return view('db-cleaner::livewire.table-report', compact('table'));
        })->name('db-cleaner.table');
    });
