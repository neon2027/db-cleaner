<?php

namespace Laravelldone\DbCleaner\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CleaningApplied
{
    use Dispatchable;

    public function __construct(
        public readonly string $table,
        public readonly string $column,
        public readonly string $type,
        public readonly array $actions,
    ) {}
}
