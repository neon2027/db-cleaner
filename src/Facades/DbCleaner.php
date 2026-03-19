<?php

namespace Laravelldone\DbCleaner\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laravelldone\DbCleaner\DTOs\TableAnalysis scan(string $table, array $columns = [])
 * @method static array scanAll()
 * @method static \Laravelldone\DbCleaner\Scoring\ScoreReport score(\Laravelldone\DbCleaner\DTOs\TableAnalysis $analysis)
 * @method static array clean(string $table, string $column, string $type, bool $confirm = false)
 * @method static array previewClean(string $table, string $column, string $type)
 * @method static array getTables()
 *
 * @see \Laravelldone\DbCleaner\DbCleaner
 */
class DbCleaner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laravelldone\DbCleaner\DbCleaner::class;
    }
}
