<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravelldone\DbCleaner\Analyzers\CasingAnalyzer;

beforeEach(function () {
    Schema::create('test_casing', function ($table) {
        $table->id();
        $table->string('department')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_casing');
});

it('detects casing inconsistencies', function () {
    DB::table('test_casing')->insert([
        ['department' => 'Engineering'],
        ['department' => 'engineering'],
        ['department' => 'ENGINEERING'],
        ['department' => 'Sales'],
    ]);

    $analyzer = new CasingAnalyzer(['casing' => ['enabled' => true]]);
    $issues = $analyzer->analyze('testing', 'test_casing', 'department', 4);

    expect($issues)->not->toBeEmpty();
    expect($issues[0]->type)->toBe('casing');
    expect($issues[0]->subtype)->toBe('inconsistent');
});

it('returns no issues when casing is consistent', function () {
    DB::table('test_casing')->insert([
        ['department' => 'Engineering'],
        ['department' => 'Sales'],
        ['department' => 'Marketing'],
    ]);

    $analyzer = new CasingAnalyzer(['casing' => ['enabled' => true]]);
    $issues = $analyzer->analyze('testing', 'test_casing', 'department', 3);

    expect($issues)->toBeEmpty();
});
