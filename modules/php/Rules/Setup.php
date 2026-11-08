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

    /**
     * Setup rules 1-3: assigns each card of a shuffled deck to its starting location.
     *
     * @param string[] $shuffledCompanies company id of each card, already shuffled
     * @param int[] $playerIds
     * @return array<int, array{company: string, location: string, location_arg: int}> in input order
     */
    public static function deal(array $shuffledCompanies, array $playerIds): array
    {
        $handsEnd = self::CARDS_REMOVED + count($playerIds) * self::HAND_SIZE;
        $cards = [];
        foreach (array_values($shuffledCompanies) as $index => $company) {
            if ($index < self::CARDS_REMOVED) {
                [$location, $arg] = [CardLocation::REMOVED, 0];
            } elseif ($index < $handsEnd) {
                $owner = $playerIds[intdiv($index - self::CARDS_REMOVED, self::HAND_SIZE)];
                [$location, $arg] = [CardLocation::HAND, $owner];
            } else {
                [$location, $arg] = [CardLocation::DECK, $index];
            }
            $cards[] = ['company' => $company, 'location' => $location, 'location_arg' => $arg];
        }
        return $cards;
    }
}
