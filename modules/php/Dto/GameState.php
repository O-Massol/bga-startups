<?php

declare(strict_types=1);

namespace Bga\Games\startups\Dto;

/** What one player may see of the game; the getAllDatas payload. */
final readonly class GameState
{
    /**
     * @param array<int, array{id: int, score: int, money: int}> $players keyed by player id
     * @param array<int, array{id: int, company: string, locationArg: int, chips: int}> $hand the viewer's own hand only
     * @param array<int, int> $handCounts cards in hand, keyed by player id
     * @param array<int, array{id: int, company: string, locationArg: int, chips: int}> $market
     * @param array<int, array<int, array{id: int, company: string, locationArg: int, chips: int}>> $portfolios keyed by player id
     * @param array<string, int|null> $chipOwners Anti-Monopoly chip holder per company id
     * @param array<string, int> $companyCardCounts
     * @param array<string, array{name: string}> $companies translatable company material
     */
    public function __construct(
        public array $players,
        public array $hand,
        public array $handCounts,
        public array $market,
        public array $portfolios,
        public array $chipOwners,
        public int $deckCount,
        public array $companyCardCounts,
        public array $companies,
    ) {
    }

    /** The framework requires getAllDatas to return an array. */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
