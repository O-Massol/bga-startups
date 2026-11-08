<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/**
 * Components rule: 6 companies [Decision], 45 cards in total; the number of cards per company
 * is also the order in which companies are scored (Scoring rule).
 */
final class Companies
{
    public const GIRAFFE_BEER = 'giraffe_beer';
    public const BOWWOW_GAMES = 'bowwow_games';
    public const FLAMINGO_SOFT = 'flamingo_soft';
    public const OCTO_COFFEE = 'octo_coffee';
    public const HIPPO_POWERTECH = 'hippo_powertech';
    public const ELEPHANT_MARS_TRAVEL = 'elephant_mars_travel';

    /** Card count per company, in scoring order (ascending). */
    public const CARD_COUNTS = [
        self::GIRAFFE_BEER => 5,
        self::BOWWOW_GAMES => 6,
        self::FLAMINGO_SOFT => 7,
        self::OCTO_COFFEE => 8,
        self::HIPPO_POWERTECH => 9,
        self::ELEPHANT_MARS_TRAVEL => 10,
    ];

    /** @return string[] company ids in scoring order */
    public static function ids(): array
    {
        return array_keys(self::CARD_COUNTS);
    }

    public static function totalCards(): int
    {
        return array_sum(self::CARD_COUNTS);
    }

    /** @return string[] one company id per physical card */
    public static function allCards(): array
    {
        $cards = [];
        foreach (self::CARD_COUNTS as $company => $count) {
            array_push($cards, ...array_fill(0, $count, $company));
        }
        return $cards;
    }
}
