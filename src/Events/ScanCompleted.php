<?php

namespace Laravelldone\DbCleaner\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Laravelldone\DbCleaner\DTOs\TableAnalysis;
use Laravelldone\DbCleaner\Models\ScanResult;

class ScanCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly TableAnalysis $analysis,
        public readonly ScanResult $scanResult,
    ) {}
}
