<div class="card">
    <div class="card-title">Clean Data</div>

    @if($successMessage)
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif

    @if($errorMessage)
        <div class="alert alert-error">{{ $errorMessage }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: flex-end; margin-bottom: 1rem;">
        <div>
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Column</label>
            <input
                type="text"
                wire:model="column"
                placeholder="e.g. name"
                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;"
            />
        </div>
        <div>
            <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Clean Type</label>
            <select wire:model="type" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                @foreach($cleanTypes as $t)
                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="previewClean" wire:loading.attr="disabled" class="btn btn-secondary">
            <span wire:loading.remove wire:target="previewClean">Preview</span>
            <span wire:loading wire:target="previewClean">Loading…</span>
        </button>
    </div>

    @if(!empty($preview))
        <div style="margin-top: 1rem;">
            <p style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Preview ({{ count($preview) }} action(s)):</p>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Affected Rows</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $action)
                        <tr>
                            <td><span class="badge badge-D">{{ $action['type'] }}</span></td>
                            <td style="font-family: monospace; font-size: 0.8rem;">{{ $action['old_value'] }}</td>
                            <td style="font-family: monospace; font-size: 0.8rem;">{{ $action['new_value'] }}</td>
                            <td>{{ number_format($action['affected_rows']) }}</td>
                            <td class="text-muted">{{ $action['description'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($showConfirm)
        <div style="margin-top: 1rem; padding: 1rem; background: #fef9c3; border: 1px solid #fde047; border-radius: 0.375rem;">
            <p style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: #92400e;">
                ⚠ This will modify database records. Are you sure?
            </p>
            <div style="display: flex; gap: 0.5rem;">
                <button wire:click="applyClean" wire:loading.attr="disabled" class="btn btn-danger">
                    <span wire:loading.remove wire:target="applyClean">Apply Cleaning</span>
                    <span wire:loading wire:target="applyClean">Applying…</span>
                </button>
                <button wire:click="cancelConfirm" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    @endif
</div>
