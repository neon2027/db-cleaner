<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravelldone\DbCleaner\Analyzers\DuplicateAnalyzer;

beforeEach(function () {
    Schema::create('test_dupes', function ($table) {
        $table->id();
        $table->string('email')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_dupes');
});

it('detects exact duplicates', function () {
    DB::table('test_dupes')->insert([
        ['email' => 'alice@example.com'],
        ['email' => 'alice@example.com'],
        ['email' => 'bob@example.com'],
    ]);

    $analyzer = new DuplicateAnalyzer([
        'duplicates' => [
            'enabled' => true,
            'exact' => true,
            'fuzzy' => false,
            'soundex' => false,
            'max_rows_for_fuzzy' => 5000,
        ],
    ]);

    $issues = $analyzer->analyze('testing', 'test_dupes', 'email', 3);

    expect($issues)->toHaveCount(1);
    expect($issues[0]->subtype)->toBe('exact');
    expect($issues[0]->value)->toBe('alice@example.com');
    expect($issues[0]->count)->toBe(2);
});

it('returns no issues when data has no duplicates', function () {
    DB::table('test_dupes')->insert([
        ['email' => 'alice@example.com'],
        ['email' => 'bob@example.com'],
    ]);

    $analyzer = new DuplicateAnalyzer([
        'duplicates' => [
            'enabled' => true,
            'exact' => true,
            'fuzzy' => false,
            'soundex' => false,
            'max_rows_for_fuzzy' => 5000,
        ],
    ]);

    $issues = $analyzer->analyze('testing', 'test_dupes', 'email', 2);

    $exactIssues = array_filter($issues, fn ($i) => $i->subtype === 'exact');
    expect($exactIssues)->toBeEmpty();
});
