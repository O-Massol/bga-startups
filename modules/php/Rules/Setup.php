<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

final class Setup
{
    public const CARDS_REMOVED = 5;
    public const HAND_SIZE = 3;
    public const STARTING_MONEY = 10;
    public const MIN_PLAYERS = 3;
    public const MAX_PLAYERS = 7;

    public static function drawPileSize(int $playerCount): int
    {
        return Companies::totalCards() - self::CARDS_REMOVED - $playerCount * self::HAND_SIZE;
    }
}
