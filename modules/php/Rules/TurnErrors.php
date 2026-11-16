<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/** User-facing messages of the turn rules, one method each so that clienttranslate() sees every literal. */
final class TurnErrors
{
    public static function cannotAffordDraw(): string
    {
        return clienttranslate('You cannot draw: you cannot pay 1 chip for every market card of a company whose chip you do not hold');
    }

    public static function drawPileEmpty(): string
    {
        return clienttranslate('The draw pile is empty');
    }

    public static function holdingCompanyChip(): string
    {
        return clienttranslate('You hold the Anti-Monopoly chip of this company: you cannot take its cards from the market');
    }

    public static function notInMarket(): string
    {
        return clienttranslate('This card is not in the market');
    }

    public static function notInYourHand(): string
    {
        return clienttranslate('This card is not in your hand');
    }

    public static function sameCompanyAsTakenCard(): string
    {
        return clienttranslate('You cannot put a card of the company you just took from the market back into the market');
    }
}
