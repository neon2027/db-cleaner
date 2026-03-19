<?php

namespace Laravelldone\DbCleaner\Cleaners;

use Illuminate\Support\Facades\DB;
use Laravelldone\DbCleaner\Contracts\CleanerContract;
use Laravelldone\DbCleaner\DTOs\CleaningAction;
use Laravelldone\DbCleaner\Support\DatabaseIntrospector;

class DuplicateMerger implements CleanerContract
{
    public function __construct(
        protected array $config = []
    ) {}

    public function handles(): string
    {
        return 'duplicate';
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

        $introspector = new DatabaseIntrospector($this->config);
        $pk = $introspector->getPrimaryKey($table);

        DB::connection($connection)->transaction(function () use ($connection, $table, $column, $actions, $pk) {
            foreach ($actions as $action) {
                // Find the "canonical" (keeper) row: lowest PK (oldest row)
                $keepId = DB::connection($connection)
                    ->table($table)
                    ->where($column, $action->oldValue)
                    ->min($pk);

                // Delete duplicates (keep the one with lowest PK)
                DB::connection($connection)
                    ->table($table)
                    ->where($column, $action->oldValue)
                    ->where($pk, '!=', $keepId)
                    ->delete();
            }
        });

        return $actions;
    }

    protected function buildActions(string $connection, string $table, string $column): array
    {
        $duplicates = DB::connection($connection)
            ->table($table)
            ->select([$column, DB::raw('COUNT(*) as cnt')])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('cnt')
            ->limit(100)
            ->get();

        $actions = [];

        foreach ($duplicates as $row) {
            $value = $row->{$column};
            $count = (int) $row->cnt;
            $duplicateCount = $count - 1; // rows to be removed

            $actions[] = new CleaningAction(
                table: $table,
                column: $column,
                type: 'duplicate_merge',
                oldValue: $value,
                newValue: $value,
                affectedRows: $duplicateCount,
                description: "Remove {$duplicateCount} duplicate rows where {$column} = '{$value}' (keep 1)",
            );
        }

        return $actions;
    }
}
