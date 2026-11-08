<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

/** Anti-Monopoly chips: one per company, held by a player or nobody. */
interface ChipRepository
{
    /** @param string[] $companyIds created without an owner */
    public function createAll(array $companyIds): void;

    /** @return array<string, int|null> owner player id per company id */
    public function owners(): array;
}
