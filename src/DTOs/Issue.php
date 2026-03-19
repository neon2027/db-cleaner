<?php

namespace Laravelldone\DbCleaner\DTOs;

class Issue
{
    public function __construct(
        public readonly string $type,       // 'duplicate' | 'whitespace' | 'casing' | 'typo'
        public readonly string $subtype,    // 'exact' | 'fuzzy' | 'soundex' | 'leading' | ...
        public readonly mixed $value,       // the problematic value
        public readonly mixed $suggestion,  // suggested fix (null if none)
        public readonly int $count,         // how many rows are affected
        public readonly array $rowIds = [], // sample affected primary key values
        public readonly array $meta = [],   // extra context (e.g., matched canonical)
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'subtype' => $this->subtype,
            'value' => $this->value,
            'suggestion' => $this->suggestion,
            'count' => $this->count,
            'row_ids' => $this->rowIds,
            'meta' => $this->meta,
        ];
    }
}
