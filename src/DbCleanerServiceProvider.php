<?php

namespace Laravelldone\DbCleaner;

use Laravelldone\DbCleaner\Commands\CleanCommand;
use Laravelldone\DbCleaner\Commands\ReportCommand;
use Laravelldone\DbCleaner\Commands\ScanCommand;
use Laravelldone\DbCleaner\Livewire\CleanerPanel;
use Laravelldone\DbCleaner\Livewire\Dashboard;
use Laravelldone\DbCleaner\Livewire\ScanRunner;
use Laravelldone\DbCleaner\Livewire\TableReport;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DbCleanerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('db-cleaner')
            ->hasConfigFile()
            ->hasMigration('create_db_cleaner_scan_results_table')
            ->hasCommands([
                ScanCommand::class,
                CleanCommand::class,
                ReportCommand::class,
            ])
            ->hasViews()
            ->hasRoute('api')
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(DbCleaner::class, function ($app) {
            return new DbCleaner(
                $app['config']->get('db-cleaner', [])
            );
        });

        $this->app->alias(DbCleaner::class, 'db-cleaner');
    }

    public function packageBooted(): void
    {
        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        try {
            Livewire::component('db-cleaner::dashboard', Dashboard::class);
            Livewire::component('db-cleaner::table-report', TableReport::class);
            Livewire::component('db-cleaner::scan-runner', ScanRunner::class);
            Livewire::component('db-cleaner::cleaner-panel', CleanerPanel::class);
        } catch (\Throwable) {
            // Livewire not fully booted (e.g. in test environments without Livewire)
        }
    }
}
