<?php

declare(strict_types=1);

namespace Bga\Games\startups\Tests\Fakes;

use Bga\Games\startups\Repository\CardRepository;

final class InMemoryCardRepository implements CardRepository
{
    /** @var array<int, array{id: int, company: string, location: string, locationArg: int, chips: int}> */
    public array $rows = [];

    public function insertAll(array $cards): void
    {
        foreach ($cards as $c) {
            $this->rows[] = [
                'id' => count($this->rows) + 1,
                'company' => $c['company'],
                'location' => $c['location'],
                'locationArg' => $c['location_arg'],
                'chips' => 0,
            ];
        }
    }

    public function at(string $location, ?int $locationArg = null): array
    {
        $found = array_filter(
            $this->rows,
            fn($r) => $r['location'] === $location && ($locationArg === null || $r['locationArg'] === $locationArg)
        );
        usort($found, fn($a, $b) => [$a['locationArg'], $a['id']] <=> [$b['locationArg'], $b['id']]);
        return array_map(fn($r) => [
            'id' => $r['id'],
            'company' => $r['company'],
            'locationArg' => $r['locationArg'],
            'chips' => $r['chips'],
        ], $found);
    }

    public function countAt(string $location): int
    {
        return count($this->at($location));
    }

    public function countsByLocationArg(string $location): array
    {
        $counts = [];
        foreach ($this->at($location) as $r) {
            $counts[$r['locationArg']] = ($counts[$r['locationArg']] ?? 0) + 1;
        }
        return $counts;
    }
}
