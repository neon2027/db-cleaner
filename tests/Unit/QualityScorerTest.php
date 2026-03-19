<?php

use Laravelldone\DbCleaner\DTOs\ColumnAnalysis;
use Laravelldone\DbCleaner\DTOs\Issue;
use Laravelldone\DbCleaner\DTOs\TableAnalysis;
use Laravelldone\DbCleaner\Scoring\QualityScorer;

it('gives a perfect score when there are no issues', function () {
    $scorer = new QualityScorer();

    $column = new ColumnAnalysis(
        table: 'users',
        column: 'name',
        dataType: 'varchar',
        totalRows: 100,
        nullCount: 0,
        issues: [],
    );

    $score = $scorer->scoreColumn($column);

    expect($score)->toBe(100.0);
});

it('reduces score proportionally for whitespace issues', function () {
    $scorer = new QualityScorer();

    $column = new ColumnAnalysis(
        table: 'users',
        column: 'name',
        dataType: 'varchar',
        totalRows: 100,
        nullCount: 0,
        issues: [
            new Issue('whitespace', 'trailing', null, 'RTRIM', 50),
        ],
    );

    $score = $scorer->scoreColumn($column);

    expect($score)->toBeLessThan(100.0);
    expect($score)->toBeGreaterThan(0.0);
});

it('assigns correct grades', function () {
    $scorer = new QualityScorer();

    expect($scorer->grade(97))->toBe('A');
    expect($scorer->grade(88))->toBe('B');
    expect($scorer->grade(75))->toBe('C');
    expect($scorer->grade(55))->toBe('D');
    expect($scorer->grade(30))->toBe('F');
});

it('scores a table as average of column scores', function () {
    $scorer = new QualityScorer();

    $col1 = new ColumnAnalysis('t', 'c1', 'varchar', 100, 0, [], 100.0, 'A');
    $col2 = new ColumnAnalysis('t', 'c2', 'varchar', 100, 0, [], 60.0, 'D');

    $table = new TableAnalysis('t', 100, [$col1, $col2]);
    $report = $scorer->score($table);

    expect($report->overallScore)->toBe(80.0);
});

it('returns perfect score for empty table', function () {
    $scorer = new QualityScorer();

    $column = new ColumnAnalysis('t', 'c', 'varchar', 0, 0, []);
    $score = $scorer->scoreColumn($column);

    expect($score)->toBe(100.0);
});
