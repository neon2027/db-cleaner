<div>
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
        <a href="{{ route('db-cleaner.dashboard') }}" style="color: #64748b; text-decoration: none;">← Back</a>
        <h1 style="font-size: 1.5rem; font-weight: 700;">{{ $table }}</h1>
    </div>

    @if($analysis)
        <div class="grid-4" style="margin-bottom: 1.5rem;">
            <div class="card" style="text-align: center;">
                <div class="stat-label">Quality Score</div>
                <div class="stat-value" style="color: {{ $analysis['quality_score'] >= 85 ? '#16a34a' : ($analysis['quality_score'] >= 70 ? '#d97706' : '#dc2626') }};">
                    {{ number_format($analysis['quality_score'], 1) }}
                </div>
                <span class="badge badge-{{ $analysis['grade'] }}">{{ $analysis['grade'] }}</span>
            </div>
            <div class="card" style="text-align: center;">
                <div class="stat-label">Total Rows</div>
                <div class="stat-value">{{ number_format($analysis['total_rows']) }}</div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="stat-label">Total Issues</div>
                <div class="stat-value" style="color: #dc2626;">{{ number_format($analysis['total_issues']) }}</div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="stat-label">Columns Analyzed</div>
                <div class="stat-value">{{ count($analysis['columns']) }}</div>
            </div>
        </div>

        @if(!empty($history))
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-title">Score History</div>
            <x-db-cleaner::chart
                id="historyChart"
                type="line"
                :data="json_encode([
                    'labels' => array_column($history, 'scanned_at'),
                    'datasets' => [[
                        'label' => 'Quality Score',
                        'data' => array_column($history, 'score'),
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
                height="200px"
            />
        </div>
        @endif

        <div class="card">
            <div class="card-title">Column Analysis</div>
            <table>
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Type</th>
                        <th>Score</th>
                        <th>Grade</th>
                        <th>Duplicates</th>
                        <th>Whitespace</th>
                        <th>Casing</th>
                        <th>Typos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis['columns'] as $col)
                        <tr>
                            <td style="font-weight: 500;">{{ $col['column'] }}</td>
                            <td class="text-muted">{{ $col['data_type'] }}</td>
                            <td>{{ number_format($col['quality_score'], 1) }}</td>
                            <td><span class="badge badge-{{ $col['grade'] }}">{{ $col['grade'] }}</span></td>
                            <td>{{ collect($col['issues'])->where('type', 'duplicate')->sum('count') }}</td>
                            <td>{{ collect($col['issues'])->where('type', 'whitespace')->sum('count') }}</td>
                            <td>{{ collect($col['issues'])->where('type', 'casing')->sum('count') }}</td>
                            <td>{{ collect($col['issues'])->where('type', 'typo')->sum('count') }}</td>
                            <td>
                                @if($col['total_issues'] > 0)
                                    <button onclick="document.getElementById('cleaner-section').scrollIntoView({behavior:'smooth'})" style="background: none; border: none; color: #3b82f6; cursor: pointer; font-size: 0.875rem;">Clean</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <p class="text-muted">No scan results for this table. Run <code>php artisan db-cleaner:scan --table={{ $table }}</code> first.</p>
        </div>
    @endif

    <div id="cleaner-section">
        <livewire:db-cleaner.cleaner-panel :table="$table" />
    </div>
</div>
