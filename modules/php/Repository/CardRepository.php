<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

interface CardRepository
{
    /** @param array<int, array{company: string, location: string, location_arg: int}> $cards */
    public function insertAll(array $cards): void;

    /** @return array<int, array{id: int, company: string, locationArg: int, chips: int}> ordered by location_arg then id */
    public function at(string $location, ?int $locationArg = null): array;

    public function countAt(string $location): int;

    /** @return array<int, int> number of cards at a location, keyed by location_arg */
    public function countsByLocationArg(string $location): array;

    /**
     * @return array{id: int, company: string, location: string, locationArg: int, chips: int}
     * @throws \InvalidArgumentException if the card does not exist
     */
    public function get(int $cardId): array;

    public function move(int $cardId, string $location, int $locationArg): void;

    /** Sets the number of Capital chips lying on a market card. */
    public function setChips(int $cardId, int $chips): void;

    /**
     * @return array{id: int, company: string, locationArg: int, chips: int} first card of the draw pile
     * @throws \UnderflowException if the draw pile is empty
     */
    public function topOfDeck(): array;
}
