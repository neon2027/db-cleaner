<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    | The connection to scan. Null uses the default connection.
    */
    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Tables Whitelist
    |--------------------------------------------------------------------------
    | Specify tables and optionally columns to scan. An empty array means all
    | user tables are scanned. Format:
    |
    | 'tables' => [
    |     'users' => ['name', 'email'],   // specific columns
    |     'products',                      // all string columns
    | ]
    */
    'tables' => [],

    /*
    |--------------------------------------------------------------------------
    | Excluded Tables
    |--------------------------------------------------------------------------
    | Tables that will never be scanned. System tables are auto-excluded.
    */
    'exclude_tables' => [
        'migrations',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'cache',
        'cache_locks',
        'personal_access_tokens',
        'password_reset_tokens',
        'db_cleaner_scan_results',
    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection
    |--------------------------------------------------------------------------
    */
    'duplicates' => [
        'enabled' => true,
        'exact' => true,
        'fuzzy' => true,
        'fuzzy_threshold' => 2,          // max levenshtein distance
        'soundex' => true,
        'max_rows_for_fuzzy' => 5000,    // skip fuzzy on large tables
    ],

    /*
    |--------------------------------------------------------------------------
    | Whitespace Detection
    |--------------------------------------------------------------------------
    */
    'whitespace' => [
        'enabled' => true,
        'leading' => true,
        'trailing' => true,
        'double_spaces' => true,
        'tabs' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Casing Inconsistencies
    |--------------------------------------------------------------------------
    */
    'casing' => [
        'enabled' => true,
        'normalize_to' => 'most_frequent', // 'most_frequent' | 'lower' | 'upper' | 'title'
    ],

    /*
    |--------------------------------------------------------------------------
    | Typo Detection
    |--------------------------------------------------------------------------
    */
    'typos' => [
        'enabled' => true,
        'similarity_threshold' => 85,    // similar_text percentage (0-100)
        'min_frequency' => 2,            // reference value must appear >= N times
        'max_rows_for_typos' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Scoring Weights
    |--------------------------------------------------------------------------
    | Weights determine how each issue type affects the overall quality score.
    | Values are relative (they are normalized internally).
    */
    'scoring_weights' => [
        'duplicates' => 30,
        'whitespace' => 20,
        'casing' => 25,
        'typos' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    | How many rows to fetch at a time for PHP-side analysis.
    */
    'batch_size' => 500,

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'prefix' => 'api/db-cleaner',
        'middleware' => ['api'],
        'auth_token' => env('DB_CLEANER_API_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled' => true,
        'prefix' => 'db-cleaner',
        'middleware' => ['web'],
    ],

];
