<?php

declare(strict_types=1);

namespace Bga\Games\startups\Tests\Fakes;

use Bga\Games\startups\Repository\CardRepository;
use Bga\Games\startups\Rules\CardLocation;

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

    public function get(int $cardId): array
    {
        foreach ($this->rows as $r) {
            if ($r['id'] === $cardId) {
                return $r;
            }
        }
        throw new \InvalidArgumentException("Unknown card $cardId");
    }

    public function move(int $cardId, string $location, int $locationArg): void
    {
        foreach ($this->rows as &$r) {
            if ($r['id'] === $cardId) {
                $r['location'] = $location;
                $r['locationArg'] = $locationArg;
                return;
            }
        }
        throw new \InvalidArgumentException("Unknown card $cardId");
    }

    public function setChips(int $cardId, int $chips): void
    {
        foreach ($this->rows as &$r) {
            if ($r['id'] === $cardId) {
                $r['chips'] = $chips;
                return;
            }
        }
        throw new \InvalidArgumentException("Unknown card $cardId");
    }

    public function topOfDeck(): array
    {
        $deck = $this->at(CardLocation::DECK);
        if ($deck === []) {
            throw new \UnderflowException('The draw pile is empty');
        }
        return $deck[0];
    }
}
