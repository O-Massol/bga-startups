<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/** Moves played for a player who left the game, so that the others can finish. */
final class ZombieChoice
{
    /**
     * Draws whenever the player can afford it, otherwise takes the first allowed market card.
     * One of the two is always possible (see TakeStepTest).
     *
     * @param array<int, array{id: int, company: string}> $market market cards in display order
     * @param string[] $heldChipCompanies companies whose Anti-Monopoly chip the player holds
     * @return int|null id of the market card to take, null to draw from the pile
     */
    public static function marketCardToTake(int $money, array $market, array $heldChipCompanies): ?int
    {
        if (DrawRules::canDraw($money, array_column($market, 'company'), $heldChipCompanies)) {
            return null;
        }
        foreach ($market as $card) {
            if (MarketRules::canTake($card['company'], $heldChipCompanies)) {
                return $card['id'];
            }
        }
        throw new \LogicException('Neither drawing nor taking from the market is possible');
    }

    /**
     * @param int[] $handCardIds
     * @return int id of the card to put in the portfolio
     */
    public static function cardToPlay(array $handCardIds): int
    {
        return $handCardIds[array_key_first($handCardIds)];
    }
}
