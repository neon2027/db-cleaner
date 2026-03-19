<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_scan_cmd', function ($table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
    });

    DB::table('test_scan_cmd')->insert([
        ['name' => ' Alice ', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'alice@example.com'],
    ]);
});

afterEach(function () {
    Schema::dropIfExists('test_scan_cmd');
});

it('runs the scan command on a specific table', function () {
    $this->artisan('db-cleaner:scan', ['--table' => 'test_scan_cmd'])
        ->assertExitCode(0);
});

it('stores a scan result in the database', function () {
    $this->artisan('db-cleaner:scan', [
        '--table' => 'test_scan_cmd',
        '--columns' => 'name,email',
    ])->assertExitCode(0);

    $this->assertDatabaseHas('db_cleaner_scan_results', [
        'table_name' => 'test_scan_cmd',
    ]);
});
