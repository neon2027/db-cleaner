<div class="card">
    <div class="card-title">Run a Scan</div>

    @if($lastError)
        <div class="alert alert-error">{{ $lastError }}</div>
    @endif

    @if($lastResult)
        <div class="alert alert-success">
            Scan complete for <strong>{{ $lastResult['table'] }}</strong> —
            Score: <strong>{{ $lastResult['score'] }}/100</strong>
            (Grade {{ $lastResult['grade'] }}), {{ $lastResult['issues'] }} issue(s) found.
        </div>
    @endif

    <div style="display: flex; gap: 1rem; align-items: flex-end;">
        <div style="flex: 1;">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Table Name</label>
            <input
                type="text"
                wire:model="table"
                placeholder="e.g. users"
                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;"
            />
        </div>
        <button
            wire:click="scan"
            wire:loading.attr="disabled"
            class="btn btn-primary"
        >
            <span wire:loading.remove wire:target="scan">▶ Run Scan</span>
            <span wire:loading wire:target="scan">Scanning…</span>
        </button>
    </div>
</div>
