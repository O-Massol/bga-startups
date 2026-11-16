<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/** Turn rule 1a: drawing from the pile costs 1 chip per market card, except for companies whose chip you hold. */
final class DrawRules
{
    /**
     * @param string[] $marketCompanies company id of each card in the market
     * @param string[] $heldChipCompanies companies whose Anti-Monopoly chip the player holds
     */
    public static function cost(array $marketCompanies, array $heldChipCompanies): int
    {
        $payable = array_filter($marketCompanies, fn(string $company) => !in_array($company, $heldChipCompanies, true));
        return count($payable);
    }

    /** [Decision] drawing is refused unless the player can pay for every market card. */
    public static function canDraw(int $money, array $marketCompanies, array $heldChipCompanies): bool
    {
        return $money >= self::cost($marketCompanies, $heldChipCompanies);
    }
}
