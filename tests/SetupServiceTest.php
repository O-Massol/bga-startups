<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Service\SetupService;
use Bga\Games\startups\Tests\Fakes\InMemoryCardRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryChipRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SetupServiceTest extends TestCase
{
    private InMemoryCardRepository $cards;
    private InMemoryChipRepository $chips;

    protected function setUp(): void
    {
        $this->cards = new InMemoryCardRepository();
        $this->chips = new InMemoryChipRepository();
    }

    private function runSetup(int $playerCount): array
    {
        $playerIds = range(101, 100 + $playerCount);
        (new SetupService($this->cards, $this->chips))->run($playerIds, fn(array $c) => $c);
        return $playerIds;
    }

    #[Test]
    #[DataProvider('playerCounts')]
    public function run_playerCount_dealsRemovedHandsAndDeck(int $playerCount, int $deckSize): void
    {
        $playerIds = $this->runSetup($playerCount);

        $this->assertSame(5, $this->cards->countAt(CardLocation::REMOVED));
        $this->assertSame($deckSize, $this->cards->countAt(CardLocation::DECK));
        $this->assertSame(array_fill_keys($playerIds, 3), $this->cards->countsByLocationArg(CardLocation::HAND));
        $this->assertCount(45, $this->cards->rows);
    }

    public static function anyPlayerCount(): array
    {
        return [
            '3 players' => [3],
            '7 players' => [7],
        ];
    }

    public static function playerCounts(): array
    {
        return [
            '3 players' => [3, 31],
            '7 players' => [7, 19],
        ];
    }

    #[Test]
    #[DataProvider('anyPlayerCount')]
    public function run_anyPlayerCount_keepsCompanyCardCounts(int $playerCount): void
    {
        $this->runSetup($playerCount);

        $counts = array_count_values(array_column($this->cards->rows, 'company'));
        $this->assertSame(Companies::CARD_COUNTS, $counts);
    }

    #[Test]
    #[DataProvider('anyPlayerCount')]
    public function run_anyPlayerCount_numbersDeckCardsInDistinctOrder(int $playerCount): void
    {
        $this->runSetup($playerCount);

        $orders = array_column($this->cards->at(CardLocation::DECK), 'locationArg');
        $this->assertCount(count($orders), array_unique($orders));
    }

    #[Test]
    #[DataProvider('anyPlayerCount')]
    public function run_anyPlayerCount_createsOneUnownedChipPerCompany(int $playerCount): void
    {
        $this->runSetup($playerCount);

        $this->assertSame(array_fill_keys(Companies::ids(), null), $this->chips->owners());
    }
}
