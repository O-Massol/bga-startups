<?php

declare(strict_types=1);

namespace Bga\Games\startups\Service;

use Bga\Games\startups\Repository\CardRepository;
use Bga\Games\startups\Repository\ChipRepository;
use Bga\Games\startups\Repository\PlayerRepository;
use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\DrawRules;
use Bga\Games\startups\Rules\MarketRules;
use Bga\Games\startups\Rules\TurnErrors;
use Bga\Games\startups\Rules\TurnException;

/** Turn rules 1 and 2: validates and applies the moves of the active player. */
final class TurnService
{
    public function __construct(
        private CardRepository $cards,
        private ChipRepository $chips,
        private PlayerRepository $players,
    ) {
    }

    /**
     * Turn rule 1a: pays 1 chip onto each market card (except companies whose chip the player holds),
     * then moves the top card of the draw pile to the player's hand.
     *
     * @return array{cardId: int, company: string, cost: int, chippedCardIds: int[]}
     * @throws \Bga\Games\startups\Rules\TurnException
     */
    public function drawFromDeck(int $playerId): array
    {
        if ($this->cards->countAt(CardLocation::DECK) === 0) {
            throw new TurnException(TurnErrors::drawPileEmpty());
        }

        $market = $this->cards->at(CardLocation::MARKET);
        $held = $this->heldChipCompanies($playerId);
        $marketCompanies = array_column($market, 'company');
        if (!DrawRules::canDraw($this->players->money($playerId), $marketCompanies, $held)) {
            throw new TurnException(TurnErrors::cannotAffordDraw());
        }

        $chipped = array_filter($market, fn(array $card) => !in_array($card['company'], $held, true));
        foreach ($chipped as $card) {
            $this->cards->setChips($card['id'], $card['chips'] + 1);
        }
        $cost = DrawRules::cost($marketCompanies, $held);
        $this->players->addMoney($playerId, -$cost);

        $drawn = $this->cards->topOfDeck();
        $this->cards->move($drawn['id'], CardLocation::HAND, $playerId);

        return [
            'cardId' => $drawn['id'],
            'company' => $drawn['company'],
            'cost' => $cost,
            'chippedCardIds' => array_values(array_column($chipped, 'id')),
        ];
    }

    /**
     * Turn rule 1b: moves a market card to the player's hand together with the chips on it.
     *
     * @return array{cardId: int, company: string, chips: int}
     * @throws \Bga\Games\startups\Rules\TurnException
     */
    public function takeFromMarket(int $playerId, int $cardId): array
    {
        try {
            $card = $this->cards->get($cardId);
        } catch (\InvalidArgumentException) {
            throw new TurnException(TurnErrors::notInMarket());
        }
        if ($card['location'] !== CardLocation::MARKET) {
            throw new TurnException(TurnErrors::notInMarket());
        }
        if (!MarketRules::canTake($card['company'], $this->heldChipCompanies($playerId))) {
            throw new TurnException(TurnErrors::holdingCompanyChip());
        }

        $this->cards->setChips($cardId, 0);
        $this->cards->move($cardId, CardLocation::HAND, $playerId);
        $this->players->addMoney($playerId, $card['chips']);

        return ['cardId' => $cardId, 'company' => $card['company'], 'chips' => $card['chips']];
    }

    /** @return string[] companies whose Anti-Monopoly chip the player holds */
    private function heldChipCompanies(int $playerId): array
    {
        return array_keys(array_filter($this->chips->owners(), fn(?int $owner) => $owner === $playerId));
    }
}
