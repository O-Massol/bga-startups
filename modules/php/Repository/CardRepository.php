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
}
