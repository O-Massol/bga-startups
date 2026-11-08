<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

/** Minimal SQL access used by the SQL repositories; the only seam to the BGA framework. */
interface Db
{
    public function execute(string $sql): void;

    /** @return array<int|string, mixed> rows keyed by first column, or first => second column when $single */
    public function collection(string $sql, bool $single = false): array;

    public function value(string $sql): mixed;

    public function quote(string $value): string;
}
