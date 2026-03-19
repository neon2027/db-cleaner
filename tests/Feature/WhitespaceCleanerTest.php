<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravelldone\DbCleaner\Cleaners\WhitespaceCleaner;

beforeEach(function () {
    Schema::create('test_clean_ws', function ($table) {
        $table->id();
        $table->string('name')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_clean_ws');
});

it('previews whitespace cleaning without modifying data', function () {
    DB::table('test_clean_ws')->insert([
        ['name' => ' Alice '],
        ['name' => 'Bob'],
    ]);

    $cleaner = new WhitespaceCleaner;
    $actions = $cleaner->preview('testing', 'test_clean_ws', 'name');

    expect($actions)->not->toBeEmpty();

    // Data should not be modified
    $row = DB::table('test_clean_ws')->where('id', 1)->first();
    expect($row->name)->toBe(' Alice ');
});

it('applies whitespace cleaning within a transaction', function () {
    DB::table('test_clean_ws')->insert([
        ['name' => ' Alice '],
        ['name' => 'Bob  Jones'],
    ]);

    $cleaner = new WhitespaceCleaner;
    $actions = $cleaner->apply('testing', 'test_clean_ws', 'name');

    expect($actions)->not->toBeEmpty();

    $alice = DB::table('test_clean_ws')->where('id', 1)->first();
    expect($alice->name)->toBe('Alice');

    $bob = DB::table('test_clean_ws')->where('id', 2)->first();
    expect($bob->name)->toBe('Bob Jones');
});

it('returns empty actions when no whitespace issues exist', function () {
    DB::table('test_clean_ws')->insert([
        ['name' => 'Alice'],
        ['name' => 'Bob'],
    ]);

    $cleaner = new WhitespaceCleaner;
    $actions = $cleaner->preview('testing', 'test_clean_ws', 'name');

    expect($actions)->toBeEmpty();
});
