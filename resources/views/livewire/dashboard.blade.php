<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700;">Database Quality Dashboard</h1>
        <button wire:click="refresh" class="btn btn-secondary">
            <span wire:loading.remove wire:target="refresh">↻ Refresh</span>
            <span wire:loading wire:target="refresh">Refreshing…</span>
        </button>
    </div>

    {{-- Summary cards --}}
    <div class="grid-4" style="margin-bottom: 1.5rem;">
        <div class="card" style="text-align: center;">
            <div class="stat-label">Overall Score</div>
            <div class="stat-value" style="color: {{ $averageScore >= 85 ? '#16a34a' : ($averageScore >= 70 ? '#d97706' : '#dc2626') }};">
                {{ $averageScore }}
            </div>
            <span class="badge badge-{{ $overallGrade }}">Grade {{ $overallGrade }}</span>
        </div>
        <div class="card" style="text-align: center;">
            <div class="stat-label">Total Issues</div>
            <div class="stat-value" style="color: #dc2626;">{{ number_format($totalIssues) }}</div>
        </div>
        <div class="card" style="text-align: center;">
            <div class="stat-label">Tables Scanned</div>
            <div class="stat-value">{{ count($tables) }}</div>
        </div>
        <div class="card" style="text-align: center;">
            <div class="stat-label">Duplicates</div>
            <div class="stat-value" style="color: #7c3aed;">{{ number_format($issueBreakdown['duplicates'] ?? 0) }}</div>
        </div>
    </div>

    <div class="grid-3">
        {{-- Issue breakdown chart --}}
        <div class="card">
            <div class="card-title">Issue Breakdown</div>
            @if(array_sum($issueBreakdown) > 0)
                <x-db-cleaner::chart
                    id="issueChart"
                    type="doughnut"
                    :data="json_encode([
                        'labels' => ['Duplicates', 'Whitespace', 'Casing', 'Typos'],
                        'datasets' => [[
                            'data' => array_values($issueBreakdown),
                            'backgroundColor' => ['#7c3aed', '#3b82f6', '#f59e0b', '#ef4444'],
                        ]],
                    ])"
                    :options="json_encode(['responsive' => true, 'maintainAspectRatio' => false])"
                    height="220px"
                />
            @else
                <p class="text-muted" style="text-align: center; padding: 2rem;">No issues detected yet. Run a scan first.</p>
            @endif
        </div>

        {{-- Score trend --}}
        <div class="card" style="grid-column: span 2;">
            <div class="card-title">Quality Score Trend</div>
            @if(!empty($scoreHistory))
                <x-db-cleaner::chart
                    id="trendChart"
                    type="line"
                    :data="json_encode([
                        'labels' => array_column($scoreHistory, 'date'),
                        'datasets' => [[
                            'label' => 'Average Score',
                            'data' => array_column($scoreHistory, 'score'),
                            'borderColor' => '#3b82f6',
                            'backgroundColor' => 'rgba(59,130,246,0.1)',
                            'fill' => true,
                            'tension' => 0.3,
                        ]],
                    ])"
                    :options="json_encode([
                        'responsive' => true,
                        'maintainAspectRatio' => false,
                        'scales' => ['y' => ['min' => 0, 'max' => 100]],
                    ])"
                    height="220px"
                />
            @else
                <p class="text-muted" style="text-align: center; padding: 2rem;">No scan history yet.</p>
            @endif
        </div>
    </div>

    {{-- Tables list --}}
    <div class="card">
        <div class="card-title">Tables</div>
        @if(empty($tables))
            <p class="text-muted">No scan results found. Run <code>php artisan db-cleaner:scan</code> to get started.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Score</th>
                        <th>Grade</th>
                        <th>Issues</th>
                        <th>Last Scan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tables as $t)
                        <tr>
                            <td style="font-weight: 500;">{{ $t['name'] }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="flex: 1; background: #e2e8f0; border-radius: 9999px; height: 6px; max-width: 100px;">
                                        <div style="width: {{ $t['score'] }}%; background: {{ $t['score'] >= 85 ? '#16a34a' : ($t['score'] >= 70 ? '#d97706' : '#dc2626') }}; height: 6px; border-radius: 9999px;"></div>
                                    </div>
                                    <span>{{ number_format($t['score'], 1) }}</span>
                                </div>
                            </td>
                            <td><span class="badge badge-{{ $t['grade'] }}">{{ $t['grade'] }}</span></td>
                            <td>{{ $t['issues'] }}</td>
                            <td class="text-muted">{{ $t['scanned_at'] }}</td>
                            <td>
                                <a href="{{ route('db-cleaner.table', $t['name']) }}" style="color: #3b82f6; text-decoration: none; font-size: 0.875rem;">View →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
