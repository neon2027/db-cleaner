<x-db-cleaner::layout :title="'Table: ' . $table">
    <livewire:db-cleaner.table-report :table="$table" />
    <div id="cleaner-section">
        <livewire:db-cleaner.cleaner-panel :table="$table" />
    </div>
</x-db-cleaner::layout>
