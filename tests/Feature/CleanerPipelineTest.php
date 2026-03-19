<?php

use Laravelldone\DbCleaner\Cleaners\CleanerPipeline;

it('throws without explicit confirmation', function () {
    $pipeline = new CleanerPipeline(['connection' => 'testing']);

    expect(fn () => $pipeline->clean('users', 'name', 'whitespace', confirm: false))
        ->toThrow(\RuntimeException::class, 'explicit confirmation');
});

it('throws for unknown cleaner type', function () {
    $pipeline = new CleanerPipeline(['connection' => 'testing']);

    expect(fn () => $pipeline->clean('users', 'name', 'unknown_type', confirm: true))
        ->toThrow(\InvalidArgumentException::class);
});

it('lists available clean types', function () {
    $pipeline = new CleanerPipeline();

    $types = $pipeline->availableTypes();

    expect($types)->toContain('whitespace');
    expect($types)->toContain('casing');
    expect($types)->toContain('duplicate');
});
