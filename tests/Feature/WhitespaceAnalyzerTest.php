<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravelldone\DbCleaner\Analyzers\WhitespaceAnalyzer;

beforeEach(function () {
    Schema::create('test_ws', function ($table) {
        $table->id();
        $table->string('name')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_ws');
});

it('detects leading whitespace', function () {
    DB::table('test_ws')->insert([
        ['name' => ' Alice'],
        ['name' => 'Bob'],
    ]);

    $analyzer = new WhitespaceAnalyzer(['whitespace' => ['enabled' => true, 'leading' => true, 'trailing' => false, 'double_spaces' => false, 'tabs' => false]]);
    $issues = $analyzer->analyze('testing', 'test_ws', 'name', 2);

    expect($issues)->toHaveCount(1);
    expect($issues[0]->subtype)->toBe('leading');
    expect($issues[0]->count)->toBe(1);
});

it('detects trailing whitespace', function () {
    DB::table('test_ws')->insert([
        ['name' => 'Alice '],
        ['name' => 'Bob'],
    ]);

    $analyzer = new WhitespaceAnalyzer(['whitespace' => ['enabled' => true, 'leading' => false, 'trailing' => true, 'double_spaces' => false, 'tabs' => false]]);
    $issues = $analyzer->analyze('testing', 'test_ws', 'name', 2);

    expect($issues)->toHaveCount(1);
    expect($issues[0]->subtype)->toBe('trailing');
});

it('detects double spaces', function () {
    DB::table('test_ws')->insert([
        ['name' => 'John  Doe'],
        ['name' => 'Jane Doe'],
    ]);

    $analyzer = new WhitespaceAnalyzer(['whitespace' => ['enabled' => true, 'leading' => false, 'trailing' => false, 'double_spaces' => true, 'tabs' => false]]);
    $issues = $analyzer->analyze('testing', 'test_ws', 'name', 2);

    expect($issues)->toHaveCount(1);
    expect($issues[0]->subtype)->toBe('double_spaces');
});

it('returns no issues when data is clean', function () {
    DB::table('test_ws')->insert([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $analyzer = new WhitespaceAnalyzer(['whitespace' => ['enabled' => true, 'leading' => true, 'trailing' => true, 'double_spaces' => true, 'tabs' => true]]);
    $issues = $analyzer->analyze('testing', 'test_ws', 'name', 2);

    expect($issues)->toBeEmpty();
});
