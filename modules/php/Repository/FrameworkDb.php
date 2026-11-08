<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

use Closure;

/** Db backed by the framework's Table methods, passed in as closures. */
final class FrameworkDb implements Db
{
    public function __construct(
        private Closure $execute,
        private Closure $collection,
        private Closure $value,
        private Closure $quote,
    ) {
    }

    public function execute(string $sql): void
    {
        ($this->execute)($sql);
    }

    public function collection(string $sql, bool $single = false): array
    {
        return ($this->collection)($sql, $single);
    }

    public function value(string $sql): mixed
    {
        return ($this->value)($sql);
    }

    public function quote(string $value): string
    {
        return ($this->quote)($value);
    }
}
