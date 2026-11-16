<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\ZombieChoice;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ZombieChoiceTest extends TestCase
{
    #[Test]
    public function marketCardToTake_emptyMarket_drawsFromThePile(): void
    {
        $this->assertNull(ZombieChoice::marketCardToTake(10, [], []));
    }

    #[Test]
    public function marketCardToTake_canAffordEveryMarketCard_drawsFromThePile(): void
    {
        $market = [['id' => 1, 'company' => Companies::GIRAFFE_BEER], ['id' => 2, 'company' => Companies::OCTO_COFFEE]];

        $this->assertNull(ZombieChoice::marketCardToTake(2, $market, []));
    }

    #[Test]
    public function marketCardToTake_cannotAfford_takesTheFirstMarketCard(): void
    {
        $market = [['id' => 5, 'company' => Companies::GIRAFFE_BEER], ['id' => 6, 'company' => Companies::OCTO_COFFEE]];

        $this->assertSame(5, ZombieChoice::marketCardToTake(1, $market, []));
    }

    #[Test]
    public function marketCardToTake_cannotAffordAndFirstCardIsOfAHeldCompany_takesTheNextAllowedCard(): void
    {
        $market = [['id' => 5, 'company' => Companies::GIRAFFE_BEER], ['id' => 6, 'company' => Companies::OCTO_COFFEE]];

        $this->assertSame(6, ZombieChoice::marketCardToTake(0, $market, [Companies::GIRAFFE_BEER]));
    }

    #[Test]
    public function marketCardToTake_noMoneyButHoldingEveryMarketCompanyChip_drawsFromThePile(): void
    {
        $market = [['id' => 5, 'company' => Companies::GIRAFFE_BEER]];

        $this->assertNull(ZombieChoice::marketCardToTake(0, $market, [Companies::GIRAFFE_BEER]));
    }

    #[Test]
    public function cardToPlay_severalCardsInHand_isTheFirstOne(): void
    {
        $this->assertSame(7, ZombieChoice::cardToPlay([7, 3, 9]));
    }
}
