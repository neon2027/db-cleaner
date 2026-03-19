<?php

namespace Laravelldone\DbCleaner\Contracts;

use Laravelldone\DbCleaner\DTOs\TableAnalysis;
use Laravelldone\DbCleaner\Scoring\ScoreReport;

interface ScorerContract
{
    public function score(TableAnalysis $analysis): ScoreReport;
}
