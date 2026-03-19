<?php

namespace Laravelldone\DbCleaner\Cleaners;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\CleanerContract;
use Laravelldone\DbCleaner\DTOs\CleaningAction;

class CasingCleaner implements CleanerContract
{
    public function handles(): string
    {
        return 'casing';
    }

    /** @return CleaningAction[] */
    public function preview(string $connection, string $table, string $column): array
    {
        return $this->buildActions($connection, $table, $column);
    }

    /** @return CleaningAction[] */
    public function apply(string $connection, string $table, string $column): array
    {
        $actions = $this->buildActions($connection, $table, $column);

        if (empty($actions)) {
            return [];
        }

        DB::connection($connection)->transaction(function () use ($connection, $table, $column, $actions) {
            foreach ($actions as $action) {
                DB::connection($connection)
                    ->table($table)
                    ->where($column, $action->oldValue)
                    ->update([$column => $action->newValue]);
            }
        });

        return $actions;
    }

    protected function buildActions(string $connection, string $table, string $column): array
    {
        // Find value groups with inconsistent casing
        $rows = DB::connection($connection)
            ->table($table)
            ->selectRaw("LOWER({$column}) as normalized, COUNT(DISTINCT {$column}) as variant_count")
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupByRaw("LOWER({$column})")
            ->havingRaw("COUNT(DISTINCT {$column}) > 1")
            ->limit(200)
            ->get();

        $actions = [];

        foreach ($rows as $row) {
            $variants = DB::connection($connection)
                ->table($table)
                ->select([$column, DB::raw("COUNT(*) as cnt")])
                ->whereRaw("LOWER({$column}) = ?", [strtolower($row->normalized)])
                ->groupBy($column)
                ->pluck('cnt', $column)
                ->all();

            arsort($variants);
            $canonical = array_key_first($variants);

            foreach ($variants as $variant => $count) {
                if ($variant === $canonical) {
                    continue;
                }

                $actions[] = new CleaningAction(
                    table: $table,
                    column: $column,
                    type: 'casing',
                    oldValue: $variant,
                    newValue: $canonical,
                    affectedRows: (int) $count,
                    description: "Normalize casing: '{$variant}' → '{$canonical}' ({$count} rows)",
                );
            }
        }

        return $actions;
    }
}
