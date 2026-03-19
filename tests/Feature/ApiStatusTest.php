<?php

use Laravelldone\DbCleaner\Models\ScanResult;

it('returns a valid status response', function () {
    ScanResult::create([
        'table_name' => 'users',
        'quality_score' => 85.5,
        'grade' => 'B',
        'total_rows' => 100,
        'total_issues' => 10,
        'issue_breakdown' => ['duplicates' => 5, 'whitespace' => 3, 'casing' => 2, 'typos' => 0],
        'column_scores' => ['name' => 85.5],
        'connection' => 'testing',
    ]);

    $this->get('/api/db-cleaner/status')
        ->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'summary' => [
                'total_tables',
                'scanned_tables',
                'average_quality_score',
                'total_issues',
            ],
        ]);
});
