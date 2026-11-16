<?php

declare(strict_types=1);

namespace Bga\Games\startups\Tests\Support;

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\TurnException;
use Bga\Games\startups\Service\TurnService;
use Bga\Games\startups\Tests\Fakes\InMemoryCardRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryChipRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryPlayerRepository;
use PHPUnit\Framework\TestCase;

/** Two players (ME and OTHER, 10 money each), all chips unowned, no cards. */
abstract class TurnServiceTestCase extends TestCase
{
    protected const ME = 101;
    protected const OTHER = 102;

    protected InMemoryCardRepository $cards;
    protected InMemoryChipRepository $chips;
    protected InMemoryPlayerRepository $players;
    protected TurnService $service;

    protected function setUp(): void
    {
        $this->cards = new InMemoryCardRepository();
        $this->chips = new InMemoryChipRepository();
        $this->players = new InMemoryPlayerRepository();
        $this->players->createAll([
            ['id' => self::ME, 'color' => 'ff0000', 'name' => 'Me'],
            ['id' => self::OTHER, 'color' => '00ff00', 'name' => 'Other'],
        ]);
        $this->chips->createAll(Companies::ids());
        $this->service = new TurnService($this->cards, $this->chips, $this->players);
    }

    /** @return int the id of the added card */
    protected function addCard(string $company, string $location, int $locationArg = 0, int $chips = 0): int
    {
        $this->cards->insertAll([['company' => $company, 'location' => $location, 'location_arg' => $locationArg]]);
        $id = count($this->cards->rows);
        $this->cards->setChips($id, $chips);
        return $id;
    }

    protected function assertRefused(string $expectedMessage, callable $move): void
    {
        try {
            $move();
            $this->fail('Expected a TurnException');
        } catch (TurnException $e) {
            $this->assertSame($expectedMessage, $e->getMessage());
        }
    }
}
