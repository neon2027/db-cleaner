<?php

namespace Laravelldone\DbCleaner\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravelldone\DbCleaner\DTOs\TableAnalysis;
use Laravelldone\DbCleaner\Scoring\ScoreReport;

class ScanResult extends Model
{
    protected $table = 'db_cleaner_scan_results';

    protected $fillable = [
        'table_name',
        'column_name',
        'quality_score',
        'grade',
        'total_rows',
        'total_issues',
        'issue_breakdown',
        'column_scores',
        'raw_analysis',
        'connection',
    ];

    protected $casts = [
        'issue_breakdown' => 'array',
        'column_scores' => 'array',
        'raw_analysis' => 'array',
        'quality_score' => 'float',
    ];

    public static function fromAnalysis(TableAnalysis $analysis, ScoreReport $report, string $connection = 'default'): static
    {
        return static::create([
            'table_name' => $analysis->table,
            'column_name' => null,
            'quality_score' => $report->overallScore,
            'grade' => $report->grade,
            'total_rows' => $analysis->totalRows,
            'total_issues' => $analysis->totalIssueCount(),
            'issue_breakdown' => $report->issueBreakdown,
            'column_scores' => $report->columnScores,
            'raw_analysis' => $analysis->toArray(),
            'connection' => $connection,
        ]);
    }

    public function scopeForTable(Builder $query, string $table): Builder
    {
        return $query->where('table_name', $table);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeHistory(Builder $query, string $table, int $limit = 30): Builder
    {
        return $query->where('table_name', $table)
            ->orderByDesc('created_at')
            ->limit($limit);
    }
}
