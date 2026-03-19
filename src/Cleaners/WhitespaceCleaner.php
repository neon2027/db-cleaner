<?php

namespace Laravelldone\DbCleaner\Cleaners;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\CleanerContract;
use Laravelldone\DbCleaner\DTOs\CleaningAction;

class WhitespaceCleaner implements CleanerContract
{
    public function handles(): string
    {
        return 'whitespace';
    }

    /** @return CleaningAction[] */
    public function preview(string $connection, string $table, string $column): array
    {
        return $this->buildActions($connection, $table, $column, apply: false);
    }

    /** @return CleaningAction[] */
    public function apply(string $connection, string $table, string $column): array
    {
        $actions = $this->buildActions($connection, $table, $column, apply: false);

        if (empty($actions)) {
            return [];
        }

        DB::connection($connection)->transaction(function () use ($connection, $table, $column) {
            // TRIM leading/trailing whitespace
            DB::connection($connection)
                ->table($table)
                ->whereRaw("{$column} != TRIM({$column})")
                ->update([$column => DB::raw("TRIM({$column})")]);

            // Collapse double spaces — do multiple passes for safety
            for ($i = 0; $i < 5; $i++) {
                $affected = DB::connection($connection)
                    ->table($table)
                    ->where($column, 'like', '%  %')
                    ->update([$column => DB::raw("REPLACE({$column}, '  ', ' ')")]);

                if ($affected === 0) {
                    break;
                }
            }

            // Replace tabs
            DB::connection($connection)
                ->table($table)
                ->where($column, 'like', "%\t%")
                ->update([$column => DB::raw("REPLACE({$column}, char(9), ' ')")]);
        });

        return $actions;
    }

    protected function buildActions(string $connection, string $table, string $column, bool $apply): array
    {
        $actions = [];

        $leadingCount = DB::connection($connection)->table($table)
            ->whereRaw("{$column} != LTRIM({$column})")->count();

        $trailingCount = DB::connection($connection)->table($table)
            ->whereRaw("{$column} != RTRIM({$column})")->count();

        $totalTrimCount = max($leadingCount, $trailingCount, DB::connection($connection)->table($table)
            ->whereRaw("{$column} != TRIM({$column})")->count());

        if ($totalTrimCount > 0) {
            $actions[] = new CleaningAction(
                table: $table,
                column: $column,
                type: 'whitespace',
                oldValue: '(leading/trailing whitespace)',
                newValue: '(trimmed)',
                affectedRows: $totalTrimCount,
                description: "TRIM {$totalTrimCount} rows with leading/trailing whitespace in {$table}.{$column}",
            );
        }

        $doubleSpaceCount = DB::connection($connection)->table($table)
            ->where($column, 'like', '%  %')->count();

        if ($doubleSpaceCount > 0) {
            $actions[] = new CleaningAction(
                table: $table,
                column: $column,
                type: 'whitespace',
                oldValue: '(double spaces)',
                newValue: '(single space)',
                affectedRows: $doubleSpaceCount,
                description: "Collapse double spaces in {$doubleSpaceCount} rows of {$table}.{$column}",
            );
        }

        $tabCount = DB::connection($connection)->table($table)
            ->where($column, 'like', "%\t%")->count();

        if ($tabCount > 0) {
            $actions[] = new CleaningAction(
                table: $table,
                column: $column,
                type: 'whitespace',
                oldValue: '(tab characters)',
                newValue: '(space)',
                affectedRows: $tabCount,
                description: "Replace tabs with spaces in {$tabCount} rows of {$table}.{$column}",
            );
        }

        return $actions;
    }
}
