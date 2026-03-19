<?php

namespace Laravelldone\DbCleaner\Livewire;

use Laravelldone\DbCleaner\Cleaners\CleanerPipeline;
use Laravelldone\DbCleaner\Events\CleaningApplied;
use Livewire\Component;

class CleanerPanel extends Component
{
    public string $table = '';

    public string $column = '';

    public string $type = 'whitespace';

    public array $preview = [];

    public bool $previewing = false;

    public bool $applying = false;

    public bool $showConfirm = false;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public array $cleanTypes = ['whitespace', 'casing', 'duplicate'];

    public function previewClean(): void
    {
        $this->previewing = true;
        $this->successMessage = null;
        $this->errorMessage = null;

        try {
            $pipeline = new CleanerPipeline(config('db-cleaner', []));
            $this->preview = $pipeline->preview($this->table, $this->column, $this->type);
            $this->showConfirm = ! empty($this->preview);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->previewing = false;
        }
    }

    public function applyClean(): void
    {
        $this->applying = true;
        $this->errorMessage = null;

        try {
            $pipeline = new CleanerPipeline(config('db-cleaner', []));
            $actions = $pipeline->clean($this->table, $this->column, $this->type, confirm: true);

            CleaningApplied::dispatch($this->table, $this->column, $this->type, $actions);

            $affectedRows = array_sum(array_column($actions, 'affected_rows'));
            $this->successMessage = "Cleaning applied: {$affectedRows} rows updated.";
            $this->showConfirm = false;
            $this->preview = [];

            $this->dispatch('cleaning-applied', table: $this->table);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->applying = false;
        }
    }

    public function cancelConfirm(): void
    {
        $this->showConfirm = false;
        $this->preview = [];
    }

    public function render()
    {
        return view('db-cleaner::livewire.cleaner-panel');
    }
}
