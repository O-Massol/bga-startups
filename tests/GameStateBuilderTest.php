<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Service\GameStateBuilder;
use Bga\Games\startups\Service\SetupService;
use Bga\Games\startups\Tests\Fakes\InMemoryCardRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryChipRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryPlayerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GameStateBuilderTest extends TestCase
{
    private function build(int $viewer): array
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

        return (new GameStateBuilder($players, $cards, $chips))->build($viewer);
    }

    #[Test]
    public function build_afterSetup_returnsOnlyViewerHand(): void
    {
        $data = $this->build(102);

        $this->assertCount(3, $data['hand']);
        $this->assertSame([102, 102, 102], array_column($data['hand'], 'locationArg'));
        $this->assertSame([101 => 3, 102 => 3, 103 => 3], $data['handCounts']);
    }

    #[Test]
    public function build_afterSetup_exposesDeckSizeButNoDeckOrRemovedCards(): void
    {
        $data = $this->build(101);

        $this->assertSame(31, $data['deckCount']);
        $this->assertArrayNotHasKey('deck', $data);
        $this->assertArrayNotHasKey('removed', $data);
    }

    #[Test]
    public function build_afterSetup_startsPlayersWithTenMoneyAndEmptyMarket(): void
    {
        $data = $this->build(101);

        $this->assertSame([10, 10, 10], array_column($data['players'], 'money'));
        $this->assertSame([], $data['market']);
        $this->assertSame([101 => [], 102 => [], 103 => []], $data['portfolios']);
    }
}
