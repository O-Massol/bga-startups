<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Tests\Fakes\InMemoryCardRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryChipRepository;
use Bga\Games\startups\Tests\Fakes\InMemoryPlayerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** Behaviour the TurnService tests rely on; the SQL repositories must honour the same contract. */
final class InMemoryRepositoriesTest extends TestCase
{
    private function cardsWithDeck(): InMemoryCardRepository
    {
        $cards = new InMemoryCardRepository();
        $cards->insertAll([
            ['company' => Companies::OCTO_COFFEE, 'location' => CardLocation::DECK, 'location_arg' => 7],
            ['company' => Companies::GIRAFFE_BEER, 'location' => CardLocation::DECK, 'location_arg' => 6],
            ['company' => Companies::BOWWOW_GAMES, 'location' => CardLocation::HAND, 'location_arg' => 101],
        ]);
        return $cards;
    }

    #[Test]
    public function get_existingCard_returnsItWithItsLocation(): void
    {
        $this->assertSame(
            ['id' => 3, 'company' => Companies::BOWWOW_GAMES, 'location' => CardLocation::HAND, 'locationArg' => 101, 'chips' => 0],
            $this->cardsWithDeck()->get(3)
        );
    }

    #[Test]
    public function get_unknownCard_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cardsWithDeck()->get(99);
    }

    #[Test]
    public function move_card_changesLocationAndArgument(): void
    {
        $cards = $this->cardsWithDeck();

        $cards->move(3, CardLocation::PORTFOLIO, 101);

        $this->assertSame([3], array_column($cards->at(CardLocation::PORTFOLIO, 101), 'id'));
        $this->assertSame([], $cards->at(CardLocation::HAND));
    }

    #[Test]
    public function setChips_marketCard_isVisibleWhenReadingTheMarket(): void
    {
        $cards = $this->cardsWithDeck();
        $cards->move(3, CardLocation::MARKET, 0);

        $cards->setChips(3, 2);

        $this->assertSame([2], array_column($cards->at(CardLocation::MARKET), 'chips'));
    }

    #[Test]
    public function topOfDeck_deckWithCards_returnsLowestLocationArg(): void
    {
        $this->assertSame(2, $this->cardsWithDeck()->topOfDeck()['id']);
    }

    #[Test]
    public function topOfDeck_emptyDeck_throws(): void
    {
        $this->expectException(UnderflowException::class);

        (new InMemoryCardRepository())->topOfDeck();
    }

    #[Test]
    public function setOwner_company_changesOwnerAndCanClearIt(): void
    {
        $chips = new InMemoryChipRepository();
        $chips->createAll([Companies::GIRAFFE_BEER]);

        $chips->setOwner(Companies::GIRAFFE_BEER, 101);
        $this->assertSame(101, $chips->owners()[Companies::GIRAFFE_BEER]);

        $chips->setOwner(Companies::GIRAFFE_BEER, null);
        $this->assertNull($chips->owners()[Companies::GIRAFFE_BEER]);
    }

    #[Test]
    public function money_newPlayer_isStartingMoney(): void
    {
        $players = new InMemoryPlayerRepository();
        $players->createAll([['id' => 101, 'color' => 'ff0000', 'name' => 'A']]);

        $this->assertSame(10, $players->money(101));
    }

    #[Test]
    public function addMoney_positiveAndNegativeDelta_updatesOnlyThatPlayer(): void
    {
        $players = new InMemoryPlayerRepository();
        $players->createAll([
            ['id' => 101, 'color' => 'ff0000', 'name' => 'A'],
            ['id' => 102, 'color' => '00ff00', 'name' => 'B'],
        ]);

        $players->addMoney(101, 3);
        $players->addMoney(101, -1);

        $this->assertSame(12, $players->money(101));
        $this->assertSame(10, $players->money(102));
    }
}
