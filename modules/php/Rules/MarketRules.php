<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/** Turn rules 1b and 2b: restrictions on taking cards from, and putting cards into, the market. */
final class MarketRules
{
    /**
     * Turn rule 1b / Anti-Monopoly rule (i): a holder cannot take that company's market cards.
     *
     * @param string[] $heldChipCompanies companies whose Anti-Monopoly chip the player holds
     */
    public static function canTake(string $company, array $heldChipCompanies): bool
    {
        return !in_array($company, $heldChipCompanies, true);
    }

    /**
     * Turn rule 2b: after taking from the market, the same company cannot be put back into it.
     *
     * @param string|null $takenFromMarketCompany company of the card taken from the market this turn, null if drawn
     */
    public static function canPutToMarket(string $company, ?string $takenFromMarketCompany): bool
    {
        return $company !== $takenFromMarketCompany;
    }
}
