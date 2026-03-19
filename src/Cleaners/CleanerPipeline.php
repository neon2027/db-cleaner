<?php

namespace Laravelldone\DbCleaner\Cleaners;

use Laravelldone\DbCleaner\Contracts\CleanerContract;

class CleanerPipeline
{
    /** @var CleanerContract[] */
    protected array $cleaners;

    public function __construct(
        protected array $config = []
    ) {
        $this->cleaners = [
            new WhitespaceCleaner,
            new CasingCleaner,
            new DuplicateMerger($config),
        ];
    }

    protected function resolve(string $type): ?CleanerContract
    {
        foreach ($this->cleaners as $cleaner) {
            if ($cleaner->handles() === $type) {
                return $cleaner;
            }
        }

        return null;
    }

    protected function connection(): string
    {
        return $this->config['connection'] ?? config('database.default');
    }

    public function preview(string $table, string $column, string $type): array
    {
        $cleaner = $this->resolve($type);

        if (! $cleaner) {
            return [];
        }

        return array_map(
            fn ($a) => $a->toArray(),
            $cleaner->preview($this->connection(), $table, $column)
        );
    }

    public function clean(string $table, string $column, string $type, bool $confirm = false): array
    {
        if (! $confirm) {
            throw new \RuntimeException('Cleaning requires explicit confirmation. Pass $confirm = true.');
        }

        $cleaner = $this->resolve($type);

        if (! $cleaner) {
            throw new \InvalidArgumentException("No cleaner found for type: {$type}");
        }

        $actions = $cleaner->apply($this->connection(), $table, $column);

        return array_map(fn ($a) => $a->toArray(), $actions);
    }

    public function availableTypes(): array
    {
        return array_map(fn ($c) => $c->handles(), $this->cleaners);
    }
}
