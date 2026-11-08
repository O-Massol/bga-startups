<?php

declare(strict_types=1);

namespace Bga\Games\startups\Tests\Fakes;

use Bga\Games\startups\Repository\PlayerRepository;
use Bga\Games\startups\Rules\Setup;

final class InMemoryPlayerRepository implements PlayerRepository
{
    /** @var array<int, array{id: int, score: int, money: int}> */
    public array $players = [];

    public function createAll(array $players): void
    {
        foreach ($players as $p) {
            $this->players[$p['id']] = ['id' => $p['id'], 'score' => 0, 'money' => Setup::STARTING_MONEY];
        }
    }

    public function all(): array
    {
        return $this->players;
    }
}
