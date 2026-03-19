<?php

namespace Laravelldone\DbCleaner\Contracts;

use Laravelldone\DbCleaner\DTOs\CleaningAction;

interface CleanerContract
{
    /**
     * Preview the cleaning actions without applying them.
     *
     * @return CleaningAction[]
     */
    public function preview(string $connection, string $table, string $column): array;

    /**
     * Apply cleaning actions within a transaction.
     *
     * @return CleaningAction[] Applied actions
     */
    public function apply(string $connection, string $table, string $column): array;

    /**
     * Whether this cleaner handles the given type.
     */
    public function handles(): string;
}
