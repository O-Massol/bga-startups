<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

final class Setup
{
    /** Setup rule 1: 5 random cards are removed face down, whatever the player count. */
    public const CARDS_REMOVED = 5;
    /** Setup rule 2: each player starts with 3 cards in hand. */
    public const HAND_SIZE = 3;
    /** Setup rule 2: each player starts with 10 Capital chips. */
    public const STARTING_MONEY = 10;
    /** [Decision] 3 to 7 players (see rules/startups-rules.md). */
    public const MIN_PLAYERS = 3;
    public const MAX_PLAYERS = 7;

    /** Setup rule 3: what remains after removal and dealing is the draw pile. */
    public static function drawPileSize(int $playerCount): int
    {
        return Companies::totalCards() - self::CARDS_REMOVED - $playerCount * self::HAND_SIZE;
    }
}
