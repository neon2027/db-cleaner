<?php

use Laravelldone\DbCleaner\Models\ScanResult;

it('shows a warning when no scan results exist', function () {
    $this->artisan('db-cleaner:report')
        ->expectsOutputToContain('No scan results found')
        ->assertExitCode(0);
});

it('displays scan results in table format', function () {
    ScanResult::create([
        'table_name' => 'users',
        'quality_score' => 92.0,
        'grade' => 'A',
        'total_rows' => 500,
        'total_issues' => 4,
        'connection' => 'testing',
    ]);

    $this->artisan('db-cleaner:report')
        ->assertExitCode(0);
});

it('outputs json format', function () {
    ScanResult::create([
        'table_name' => 'users',
        'quality_score' => 92.0,
        'grade' => 'A',
        'total_rows' => 500,
        'total_issues' => 4,
        'connection' => 'testing',
    ]);

    $this->artisan('db-cleaner:report', ['--format' => 'json'])
        ->assertExitCode(0);
});
