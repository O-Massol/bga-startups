<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\DrawRules;
use Bga\Games\startups\Rules\MarketRules;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** Draw and market rules combined: a player can never be stuck at the start of their turn. */
final class TakeStepTest extends TestCase
{
    /** @param string[] $market @param string[] $held */
    #[Test]
    #[DataProvider('situations')]
    public function takeStep_anySituationWithNoMoney_alwaysHasAnAllowedAction(array $market, array $held): void
    {
        $canDraw = DrawRules::canDraw(0, $market, $held);
        $canTakeAny = count(array_filter($market, fn(string $c) => MarketRules::canTake($c, $held))) > 0;

        $this->assertTrue($canDraw || $canTakeAny);
    }

    public static function situations(): array
    {
        return [
            'empty market' => [[], []],
            'only held companies in market' => [[Companies::GIRAFFE_BEER], [Companies::GIRAFFE_BEER]],
            'all chips held' => [[Companies::GIRAFFE_BEER, Companies::OCTO_COFFEE], Companies::ids()],
            'mixed: one takeable card' => [
                [Companies::GIRAFFE_BEER, Companies::OCTO_COFFEE],
                [Companies::GIRAFFE_BEER],
            ],
        ];
    }
}
