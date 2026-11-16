<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\DrawRules;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DrawRulesTest extends TestCase
{
    #[Test]
    public function cost_emptyMarket_isZero(): void
    {
        $this->assertSame(0, DrawRules::cost([], []));
    }

    #[Test]
    public function cost_noChipHeld_isOnePerMarketCard(): void
    {
        $this->assertSame(3, DrawRules::cost([Companies::GIRAFFE_BEER, Companies::BOWWOW_GAMES, Companies::OCTO_COFFEE], []));
    }

    #[Test]
    public function cost_twoCardsOfSameCompany_countsEachCard(): void
    {
        $this->assertSame(2, DrawRules::cost([Companies::GIRAFFE_BEER, Companies::GIRAFFE_BEER], []));
    }

    #[Test]
    public function cost_someChipsHeld_skipsThoseCompaniesCards(): void
    {
        $this->assertSame(1, DrawRules::cost([Companies::GIRAFFE_BEER, Companies::GIRAFFE_BEER, Companies::BOWWOW_GAMES], [Companies::GIRAFFE_BEER]));
    }

    #[Test]
    public function cost_chipHeldForEveryMarketCompany_isZero(): void
    {
        $this->assertSame(0, DrawRules::cost([Companies::GIRAFFE_BEER, Companies::BOWWOW_GAMES], [Companies::GIRAFFE_BEER, Companies::BOWWOW_GAMES, Companies::OCTO_COFFEE]));
    }

    #[Test]
    public function canDraw_moneyEqualsCost_isTrue(): void
    {
        $this->assertTrue(DrawRules::canDraw(2, [Companies::GIRAFFE_BEER, Companies::BOWWOW_GAMES], []));
    }

    #[Test]
    public function canDraw_moneyBelowCost_isFalse(): void
    {
        $this->assertFalse(DrawRules::canDraw(1, [Companies::GIRAFFE_BEER, Companies::BOWWOW_GAMES], []));
    }

    #[Test]
    public function canDraw_noMoneyButNothingToPay_isTrue(): void
    {
        $this->assertTrue(DrawRules::canDraw(0, [Companies::GIRAFFE_BEER], [Companies::GIRAFFE_BEER]));
    }
}
