<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Tests\Support\TurnServiceTestCase;
use PHPUnit\Framework\Attributes\Test;

/** Read-only queries the states use for their args and for zombie turns. */
final class TurnServiceOptionsTest extends TurnServiceTestCase
{
    #[Test]
    public function takeOptions_emptyMarket_canDrawForFreeAndTakesNothing(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);

        $this->assertSame(
            ['drawCost' => 0, 'canDraw' => true, 'takeableCardIds' => []],
            $this->service->takeOptions(self::ME)
        );
    }

    #[Test]
    public function takeOptions_holdingOneCompanyChip_costSkipsItAndItsCardsAreNotTakeable(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $coffee = $this->addCard(Companies::OCTO_COFFEE, CardLocation::MARKET);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::ME);

        $this->assertSame(
            ['drawCost' => 1, 'canDraw' => true, 'takeableCardIds' => [$coffee]],
            $this->service->takeOptions(self::ME)
        );
    }

    #[Test]
    public function takeOptions_cannotPayTheCost_cannotDrawButCanTake(): void
    {
        $this->players->addMoney(self::ME, -10);
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $card = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);

        $this->assertSame(
            ['drawCost' => 1, 'canDraw' => false, 'takeableCardIds' => [$card]],
            $this->service->takeOptions(self::ME)
        );
    }

    #[Test]
    public function takeOptions_emptyDeck_cannotDraw(): void
    {
        $this->assertFalse($this->service->takeOptions(self::ME)['canDraw']);
    }

    #[Test]
    public function handCardIds_severalPlayers_returnsOnlyTheGivenPlayersHand(): void
    {
        $mine = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::OTHER);
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::PORTFOLIO, self::ME);
        $alsoMine = $this->addCard(Companies::GIRAFFE_BEER, CardLocation::HAND, self::ME);

        $this->assertSame([$mine, $alsoMine], $this->service->handCardIds(self::ME));
    }

    #[Test]
    public function zombieMarketCardToTake_canAffordTheCost_drawsFromThePile(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::DECK, 8);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);

        $this->assertNull($this->service->zombieMarketCardToTake(self::ME));
    }

    #[Test]
    public function zombieMarketCardToTake_cannotAffordAndHoldsTheFirstCardsChip_takesTheNextCard(): void
    {
        $this->players->addMoney(self::ME, -10);
        $this->addCard(Companies::GIRAFFE_BEER, CardLocation::MARKET);
        $coffee = $this->addCard(Companies::OCTO_COFFEE, CardLocation::MARKET);
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::ME);

        $this->assertSame($coffee, $this->service->zombieMarketCardToTake(self::ME));
    }
}
