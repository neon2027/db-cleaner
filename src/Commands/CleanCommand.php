<?php

namespace Laravelldone\DbCleaner\Commands;

use Illuminate\Console\Command;
use Laravelldone\DbCleaner\Cleaners\CleanerPipeline;
use Laravelldone\DbCleaner\Events\CleaningApplied;

class CleanCommand extends Command
{
    protected $signature = 'db-cleaner:clean
                            {table : The table to clean}
                            {--column= : Specific column to clean}
                            {--type= : Type of cleaning: whitespace, casing, duplicate}
                            {--dry-run : Preview actions without applying}
                            {--force : Skip confirmation prompt}
                            {--connection= : Database connection to use}';

    protected $description = 'Clean data quality issues in a database table';

    public function handle(): int
    {
        $table = $this->argument('table');
        $column = $this->option('column');
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $connection = $this->option('connection');

        if ($connection) {
            config(['db-cleaner.connection' => $connection]);
        }

        if (! $column || ! $type) {
            $this->error('Both --column and --type are required.');

            return self::FAILURE;
        }

        $pipeline = new CleanerPipeline(config('db-cleaner', []));

        if ($dryRun) {
            $this->info('[DRY RUN] Previewing cleaning actions for '.$table.'.'.$column.' (type: '.$type.')');
            $actions = $pipeline->preview($table, $column, $type);

            if (empty($actions)) {
                $this->line('No cleaning actions needed.');

                return self::SUCCESS;
            }

            $this->table(
                ['Type', 'Old Value', 'New Value', 'Affected Rows', 'Description'],
                array_map(fn ($a) => [
                    $a['type'],
                    $a['old_value'],
                    $a['new_value'],
                    $a['affected_rows'],
                    $a['description'],
                ], $actions)
            );

            return self::SUCCESS;
        }

        if (! $force && ! $this->confirm("Apply cleaning to {$table}.{$column} (type: {$type})? This will modify data.")) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        try {
            $actions = $pipeline->clean($table, $column, $type, confirm: true);

            CleaningApplied::dispatch($table, $column, $type, $actions);

            $this->info('Cleaning applied successfully.');
            $this->line(count($actions).' action(s) performed.');
        } catch (\Throwable $e) {
            $this->error('Cleaning failed: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
