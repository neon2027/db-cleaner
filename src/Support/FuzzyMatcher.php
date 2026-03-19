<?php

namespace Laravelldone\DbCleaner\Support;

class FuzzyMatcher
{
    /**
     * Group values by levenshtein proximity.
     * Returns array of [canonical => [similar_values]] groups.
     *
     * @param  array<string>  $values  Unique values to group
     */
    public static function groupByLevenshtein(array $values, int $threshold = 2): array
    {
        $groups = [];
        $assigned = [];

        foreach ($values as $i => $val) {
            if (isset($assigned[$i])) {
                continue;
            }

            $group = [$val];
            foreach ($values as $j => $other) {
                if ($i === $j || isset($assigned[$j])) {
                    continue;
                }

                if (levenshtein(strtolower($val), strtolower($other)) <= $threshold) {
                    $group[] = $other;
                    $assigned[$j] = true;
                }
            }

            if (count($group) > 1) {
                $groups[$val] = $group;
                $assigned[$i] = true;
            }
        }

        return $groups;
    }

    /**
     * Group values by soundex.
     * Returns array of [soundex_code => [values]] groups with more than 1 member.
     *
     * @param  array<string>  $values
     */
    public static function groupBySoundex(array $values): array
    {
        $groups = [];

        foreach ($values as $val) {
            if (trim($val) === '') {
                continue;
            }

            $code = soundex($val);
            $groups[$code][] = $val;
        }

        return array_filter($groups, fn ($g) => count($g) > 1);
    }

    /**
     * Find values that are similar to any high-frequency canonical value.
     * Returns array of [value => canonical] matches.
     *
     * @param  array<string, int>  $valueCounts  value => count
     * @param  int  $minFrequency  Canonical must appear >= this many times
     * @param  int  $threshold  Similarity percent (0-100)
     */
    public static function findTypoLikePairs(array $valueCounts, int $minFrequency = 2, int $threshold = 85): array
    {
        $canonicals = array_keys(array_filter($valueCounts, fn ($c) => $c >= $minFrequency));
        $rare = array_keys(array_filter($valueCounts, fn ($c) => $c < $minFrequency));

        $matches = [];

        foreach ($rare as $rareVal) {
            foreach ($canonicals as $canonical) {
                if (strtolower($rareVal) === strtolower($canonical)) {
                    continue;
                }

                similar_text(strtolower($rareVal), strtolower($canonical), $percent);

                if ($percent >= $threshold) {
                    $matches[$rareVal] = $canonical;
                    break;
                }
            }
        }

        return $matches;
    }
}
