<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

interface PlayerRepository
{
    /** @param array<int, array{id: int, color: string, name: string}> $players new players; money starts at the schema default */
    public function createAll(array $players): void;

    /** @return array<int, array{id: int, score: int, money: int}> keyed by player id */
    public function all(): array;

    public function money(int $playerId): int;

    /** @param int $delta positive to gain, negative to pay */
    public function addMoney(int $playerId, int $delta): void;
}
