<?php

namespace Laravelldone\DbCleaner\DTOs;

class CleaningAction
{
    public function __construct(
        public readonly string $table,
        public readonly string $column,
        public readonly string $type,        // 'whitespace' | 'casing' | 'duplicate_merge'
        public readonly mixed $oldValue,
        public readonly mixed $newValue,
        public readonly int $affectedRows,
        public readonly array $rowIds = [],
        public readonly string $description = '',
    ) {}

    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'column' => $this->column,
            'type' => $this->type,
            'old_value' => $this->oldValue,
            'new_value' => $this->newValue,
            'affected_rows' => $this->affectedRows,
            'row_ids' => $this->rowIds,
            'description' => $this->description,
        ];
    }
}
