<?php

namespace Laravelldone\DbCleaner\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseIntrospector
{
    public function __construct(
        protected array $config = []
    ) {}

    protected function connection(): string
    {
        return $this->config['connection'] ?? config('database.default');
    }

    public function getTablesToScan(): array
    {
        $configuredTables = $this->config['tables'] ?? [];
        $excludedTables = $this->config['exclude_tables'] ?? [];

        if (! empty($configuredTables)) {
            $tables = [];
            foreach ($configuredTables as $key => $value) {
                $tables[] = is_int($key) ? $value : $key;
            }

            return array_diff($tables, $excludedTables);
        }

        return array_diff($this->getAllUserTables(), $excludedTables);
    }

    public function getAllUserTables(): array
    {
        $connection = $this->connection();
        $driver = DB::connection($connection)->getDriverName();

        return match ($driver) {
            'mysql' => $this->getMysqlTables($connection),
            'pgsql' => $this->getPgsqlTables($connection),
            'sqlite' => $this->getSqliteTables($connection),
            default => [],
        };
    }

    protected function getMysqlTables(string $connection): array
    {
        $database = DB::connection($connection)->getDatabaseName();
        $rows = DB::connection($connection)
            ->select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?', [$database, 'BASE TABLE']);

        return array_map(fn ($r) => $r->TABLE_NAME, $rows);
    }

    protected function getPgsqlTables(string $connection): array
    {
        $rows = DB::connection($connection)
            ->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");

        return array_map(fn ($r) => $r->tablename, $rows);
    }

    protected function getSqliteTables(string $connection): array
    {
        $rows = DB::connection($connection)
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        return array_map(fn ($r) => $r->name, $rows);
    }

    public function getStringColumns(string $table): array
    {
        $connection = $this->connection();

        try {
            $columns = Schema::connection($connection)->getColumns($table);
        } catch (\Throwable) {
            return [];
        }

        $stringTypes = ['varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext', 'string'];

        return array_values(array_map(
            fn ($c) => $c['name'],
            array_filter($columns, function ($col) use ($stringTypes) {
                $type = strtolower($col['type_name'] ?? $col['type'] ?? '');

                return in_array($type, $stringTypes) || str_contains($type, 'char') || str_contains($type, 'text');
            })
        ));
    }

    public function getColumnsForTable(string $table): array
    {
        $configuredTables = $this->config['tables'] ?? [];

        foreach ($configuredTables as $key => $value) {
            if (! is_int($key) && $key === $table && is_array($value)) {
                return $value;
            }
        }

        return $this->getStringColumns($table);
    }

    public function getRowCount(string $table): int
    {
        $connection = $this->connection();

        return (int) DB::connection($connection)->table($table)->count();
    }

    public function getPrimaryKey(string $table): string
    {
        $connection = $this->connection();

        try {
            $indexes = Schema::connection($connection)->getIndexes($table);
            foreach ($indexes as $index) {
                if (($index['primary'] ?? false) && count($index['columns']) === 1) {
                    return $index['columns'][0];
                }
            }
        } catch (\Throwable) {
        }

        return 'id';
    }
}
