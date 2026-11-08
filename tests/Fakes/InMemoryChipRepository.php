<?php

declare(strict_types=1);

namespace Bga\Games\startups\Tests\Fakes;

use Bga\Games\startups\Repository\ChipRepository;

final class InMemoryChipRepository implements ChipRepository
{
    /** @var array<string, int|null> */
    public array $owners = [];

    public function createAll(array $companyIds): void
    {
        foreach ($companyIds as $id) {
            $this->owners[$id] = null;
        }
    }

    public function owners(): array
    {
        return $this->owners;
    }
}
