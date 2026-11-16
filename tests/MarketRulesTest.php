<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\MarketRules;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MarketRulesTest extends TestCase
{
    #[Test]
    public function canTake_companyChipHeld_isFalse(): void
    {
        $this->assertFalse(MarketRules::canTake(Companies::GIRAFFE_BEER, [Companies::GIRAFFE_BEER]));
    }

    #[Test]
    public function canTake_otherCompanyChipHeld_isTrue(): void
    {
        $this->assertTrue(MarketRules::canTake(Companies::GIRAFFE_BEER, [Companies::OCTO_COFFEE]));
    }

    #[Test]
    public function canTake_noChipHeld_isTrue(): void
    {
        $this->assertTrue(MarketRules::canTake(Companies::GIRAFFE_BEER, []));
    }

    #[Test]
    public function canPutToMarket_sameCompanyAsTakenCard_isFalse(): void
    {
        $this->assertFalse(MarketRules::canPutToMarket(Companies::OCTO_COFFEE, Companies::OCTO_COFFEE));
    }

    #[Test]
    public function canPutToMarket_otherCompanyThanTakenCard_isTrue(): void
    {
        $this->assertTrue(MarketRules::canPutToMarket(Companies::GIRAFFE_BEER, Companies::OCTO_COFFEE));
    }

    #[Test]
    public function canPutToMarket_nothingTakenFromMarket_isTrue(): void
    {
        $this->assertTrue(MarketRules::canPutToMarket(Companies::OCTO_COFFEE, null));
    }
}
