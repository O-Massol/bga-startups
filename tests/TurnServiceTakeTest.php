<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\TurnErrors;
use Bga\Games\startups\Tests\Support\TurnServiceTestCase;
use PHPUnit\Framework\Attributes\Test;

/** Turn rule 1: take a card, from the draw pile or from the market. */
final class TurnServiceTakeTest extends TurnServiceTestCase
{
    // --- drawFromDeck ---

    #[Test]
    public function drawFromDeck_emptyMarket_movesTopCardToHandForFree(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $top = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::DECK, 7);

        $result = $this->service->drawFromDeck(self::ME);

        $this->assertSame(['cardId' => $top, 'company' => Companies::GIRAFFE_BEER, 'cost' => 0, 'chippedCardIds' => []], $result);
        $this->assertSame([$top], array_column($this->cards->at(CardLocation::HAND, self::ME), 'id'));
        $this->assertSame(1, $this->cards->countAt(CardLocation::DECK));
        $this->assertSame(10, $this->players->money(self::ME));
    }

    #[Test]
    public function drawFromDeck_marketCards_paysOneChipOntoEachOfThem(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $first = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET, 0, 0);
        $second = $this->addCard(Companies::BOWWOW_GAMES, CardLocation::MARKET, 0, 2);

        $result = $this->service->drawFromDeck(self::ME);

        $this->assertSame(2, $result['cost']);
        $this->assertSame([$first, $second], $result['chippedCardIds']);
        $this->assertSame([1, 3], array_column($this->cards->at(CardLocation::MARKET), 'chips'));
        $this->assertSame(8, $this->players->money(self::ME));
    }

    #[Test]
    public function drawFromDeck_holdingACompanyChip_skipsThatCompanysMarketCards(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET, 0, 1);
        $games = $this->addCard(Companies::BOWWOW_GAMES, CardLocation::MARKET, 0, 0);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::ME);

        $result = $this->service->drawFromDeck(self::ME);

        $this->assertSame(1, $result['cost']);
        $this->assertSame([$games], $result['chippedCardIds']);
        $this->assertSame([1, 1], array_column($this->cards->at(CardLocation::MARKET), 'chips'));
        $this->assertSame(9, $this->players->money(self::ME));
    }

    #[Test]
    public function drawFromDeck_otherPlayerHoldsTheChip_stillPaysForThatCompany(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::OTHER);

        $this->assertSame(1, $this->service->drawFromDeck(self::ME)['cost']);
    }

    #[Test]
    public function drawFromDeck_moneyExactlyCoversTheCost_leavesZero(): void
    {
        $this->players->addMoney(self::ME, -8);
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $this->addCard(Companies::BOWWOW_GAMES, CardLocation::MARKET);

        $this->service->drawFromDeck(self::ME);

        $this->assertSame(0, $this->players->money(self::ME));
    }

    #[Test]
    public function drawFromDeck_cannotPayForEveryMarketCard_isRefusedAndNothingChanges(): void
    {
        $this->players->addMoney(self::ME, -9);
        $deckCard = $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $this->addCard(Companies::BOWWOW_GAMES, CardLocation::MARKET);

        $this->assertRefused(TurnErrors::cannotAffordDraw(), fn() => $this->service->drawFromDeck(self::ME));

        $this->assertSame(1, $this->players->money(self::ME));
        $this->assertSame([0, 0], array_column($this->cards->at(CardLocation::MARKET), 'chips'));
        $this->assertSame(CardLocation::DECK, $this->cards->get($deckCard)['location']);
    }

    #[Test]
    public function drawFromDeck_noMoneyButHoldingEveryMarketCompanyChip_isAllowed(): void
    {
        $this->players->addMoney(self::ME, -10);
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::ME);

        $result = $this->service->drawFromDeck(self::ME);

        $this->assertSame(0, $result['cost']);
        $this->assertSame(0, $this->players->money(self::ME));
    }

    #[Test]
    public function drawFromDeck_emptyDeck_isRefused(): void
    {
        $this->assertRefused(TurnErrors::drawPileEmpty(), fn() => $this->service->drawFromDeck(self::ME));
    }

    #[Test]
    public function drawFromDeck_lastCardOfThePile_leavesTheDeckEmpty(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);

        $this->service->drawFromDeck(self::ME);

        $this->assertSame(0, $this->cards->countAt(CardLocation::DECK));
    }

    // --- takeFromMarket ---

    #[Test]
    public function takeFromMarket_cardWithChips_goesToHandAndPlayerGainsTheChips(): void
    {
        $card = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET, 0, 3);
        $this->addCard(Companies::BOWWOW_GAMES, CardLocation::MARKET, 0, 2);

        $result = $this->service->takeFromMarket(self::ME, $card);

        $this->assertSame(['cardId' => $card, 'company' => Companies::GIRAFFE_BEER, 'chips' => 3], $result);
        $this->assertSame([$card], array_column($this->cards->at(CardLocation::HAND, self::ME), 'id'));
        $this->assertSame(13, $this->players->money(self::ME));
        $this->assertSame(1, $this->cards->countAt(CardLocation::MARKET));
    }

    #[Test]
    public function takeFromMarket_cardWithoutChips_leavesMoneyUnchanged(): void
    {
        $card = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);

        $this->assertSame(0, $this->service->takeFromMarket(self::ME, $card)['chips']);
        $this->assertSame(10, $this->players->money(self::ME));
    }

    #[Test]
    public function takeFromMarket_cardWithChips_cardKeepsNoChipsOnceInHand(): void
    {
        $card = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET, 0, 3);

        $this->service->takeFromMarket(self::ME, $card);

        $this->assertSame(0, $this->cards->get($card)['chips']);
    }

    #[Test]
    public function takeFromMarket_holdingTheCompanyChip_isRefusedAndNothingChanges(): void
    {
        $card = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET, 0, 3);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::ME);

        $this->assertRefused(TurnErrors::holdingCompanyChip(), fn() => $this->service->takeFromMarket(self::ME, $card));

        $this->assertSame(CardLocation::MARKET, $this->cards->get($card)['location']);
        $this->assertSame(3, $this->cards->get($card)['chips']);
        $this->assertSame(10, $this->players->money(self::ME));
    }

    #[Test]
    public function takeFromMarket_otherPlayerHoldsTheChip_isAllowed(): void
    {
        $card = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::OTHER);

        $this->assertSame($card, $this->service->takeFromMarket(self::ME, $card)['cardId']);
    }

    #[Test]
    public function takeFromMarket_cardInAnotherLocation_isRefused(): void
    {
        $inHand = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::HAND, self::OTHER);

        $this->assertRefused(TurnErrors::notInMarket(), fn() => $this->service->takeFromMarket(self::ME, $inHand));
    }

    #[Test]
    public function takeFromMarket_unknownCard_isRefused(): void
    {
        $this->assertRefused(TurnErrors::notInMarket(), fn() => $this->service->takeFromMarket(self::ME, 99));
    }
}
