<?php

declare(strict_types=1);

use Bga\Games\startups\Dto\GameState;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Service\GameStateBuilder;
use Bga\Games\startups\Service\SetupService;
use Bga\Games\startups\Tests\Fakes\InMemoryCardRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryChipRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryPlayerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GameStateBuilderTest extends TestCase
{
    private function buildState(int $viewer): GameState
    {
        $players = new InMemoryPlayerRepository();
        $cards = new InMemoryCardRepository();
        $chips = new InMemoryChipRepository();
        $players->createAll([
            ['id' => 101, 'color' => 'ff0000', 'name' => 'A'],
            ['id' => 102, 'color' => '00ff00', 'name' => 'B'],
            ['id' => 103, 'color' => '0000ff', 'name' => 'C'],
        ]);
        (new SetupService($cards, $chips))->run([101, 102, 103], fn(array $c) => $c);

        return (new GameStateBuilder($players, $cards, $chips, ['giraffe_beer' => ['name' => 'Giraffe Beer']]))->buildGameStateForPlayerId($viewer);
    }

    #[Test]
    public function buildGameStateForPlayerId_afterSetup_returnsViewerVisibleState(): void
    {
        $data = $this->buildState(102)->toArray();

        // Unshuffled deal: cards 1-5 removed, 6-8 / 9-11 / 12-14 are the hands, 15-45 the draw pile.
        $this->assertSame([
            'players' => [
                101 => ['id' => 101, 'score' => 0, 'money' => 10],
                102 => ['id' => 102, 'score' => 0, 'money' => 10],
                103 => ['id' => 103, 'score' => 0, 'money' => 10],
            ],
            'hand' => [
                ['id' => 9, 'company' => 'bowwow_games', 'locationArg' => 102, 'chips' => 0],
                ['id' => 10, 'company' => 'bowwow_games', 'locationArg' => 102, 'chips' => 0],
                ['id' => 11, 'company' => 'bowwow_games', 'locationArg' => 102, 'chips' => 0],
            ],
            'handCounts' => [101 => 3, 102 => 3, 103 => 3],
            'market' => [],
            'portfolios' => [101 => [], 102 => [], 103 => []],
            'chipOwners' => array_fill_keys(Companies::ids(), null),
            'deckCount' => 31,
            'companyCardCounts' => Companies::CARD_COUNTS,
            'companies' => ['giraffe_beer' => ['name' => 'Giraffe Beer']],
        ], $data);
    }
}
