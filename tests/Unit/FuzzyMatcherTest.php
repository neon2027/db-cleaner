<?php

use Laravelldone\DbCleaner\Support\FuzzyMatcher;

it('groups values by levenshtein distance', function () {
    $values = ['John', 'Jon', 'Jane', 'Mary'];

    $groups = FuzzyMatcher::groupByLevenshtein($values, threshold: 2);

    expect($groups)->toHaveKey('John');
    expect($groups['John'])->toContain('Jon');
    expect($groups['John'])->not->toContain('Mary');
});

it('returns empty groups when no values are within threshold', function () {
    $values = ['Alice', 'Bob', 'Charlie'];

    $groups = FuzzyMatcher::groupByLevenshtein($values, threshold: 1);

    expect($groups)->toBeEmpty();
});

it('groups values by soundex', function () {
    $values = ['Smith', 'Smyth', 'Jones'];

    $groups = FuzzyMatcher::groupBySoundex($values);

    $found = false;
    foreach ($groups as $variants) {
        if (in_array('Smith', $variants) && in_array('Smyth', $variants)) {
            $found = true;
        }
    }

    expect($found)->toBeTrue();
});

it('does not group values with different soundex', function () {
    $values = ['Alice', 'Bob'];

    $groups = FuzzyMatcher::groupBySoundex($values);

    expect($groups)->toBeEmpty();
});

it('finds typo-like pairs using similar_text', function () {
    $valueCounts = [
        'engineering' => 50,
        'enginreeing' => 1,
        'design' => 30,
        'desing' => 1,
        'sales' => 100,
    ];

    $pairs = FuzzyMatcher::findTypoLikePairs($valueCounts, minFrequency: 2, threshold: 85);

    expect($pairs)->toHaveKey('enginreeing');
    expect($pairs['enginreeing'])->toBe('engineering');
});

it('ignores case when comparing in similar_text', function () {
    $valueCounts = [
        'Engineering' => 50,
        'enginreeing' => 1,
    ];

    $pairs = FuzzyMatcher::findTypoLikePairs($valueCounts, minFrequency: 2, threshold: 85);

    expect($pairs)->toHaveKey('enginreeing');
});
