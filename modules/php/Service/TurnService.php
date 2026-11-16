<?php

declare(strict_types=1);

namespace Bga\Games\startups\Service;

use Bga\Games\startups\Repository\CardRepository;
use Bga\Games\startups\Repository\ChipRepository;
use Bga\Games\startups\Repository\PlayerRepository;
use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\ChipHolder;
use Bga\Games\startups\Rules\DrawRules;
use Bga\Games\startups\Rules\MarketRules;
use Bga\Games\startups\Rules\TurnErrors;
use Bga\Games\startups\Rules\TurnException;
use Bga\Games\startups\Rules\ZombieChoice;

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
     * What the active player may do at the start of their turn; sent to the client as state args.
     *
     * @return array{drawCost: int, canDraw: bool, takeableCardIds: int[]}
     */
    public function takeOptions(int $playerId): array
    {
        $market = $this->cards->at(CardLocation::MARKET);
        $held = $this->heldChipCompanies($playerId);
        $marketCompanies = array_column($market, 'company');
        $takeable = array_filter($market, fn(array $card) => MarketRules::canTake($card['company'], $held));

        return [
            'drawCost' => DrawRules::cost($marketCompanies, $held),
            'canDraw' => $this->cards->countAt(CardLocation::DECK) > 0
                && DrawRules::canDraw($this->players->money($playerId), $marketCompanies, $held),
            'takeableCardIds' => array_values(array_column($takeable, 'id')),
        ];
    }

    /** @return int[] ids of the cards in the player's hand */
    public function handCardIds(int $playerId): array
    {
        return array_column($this->cards->at(CardLocation::HAND, $playerId), 'id');
    }

    /** @return int|null market card a zombie takes, null if it draws (see ZombieChoice::marketCardToTake) */
    public function zombieMarketCardToTake(int $playerId): ?int
    {
        return ZombieChoice::marketCardToTake(
            $this->players->money($playerId),
            $this->cards->at(CardLocation::MARKET),
            $this->heldChipCompanies($playerId)
        );
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

    /**
     * Turn rule 2a: moves a card from the player's hand to their portfolio, then re-evaluates the company's
     * Anti-Monopoly chip.
     *
     * @return array{cardId: int, company: string, chipChange: array{company: string, from: int|null, to: int}|null}
     *         chipChange is set when the chip changed hands (from = null if it was unowned)
     * @throws TurnException
     */
    public function playToPortfolio(int $playerId, int $cardId): array
    {
        $card = $this->cardInHand($playerId, $cardId);
        $this->cards->move($cardId, CardLocation::PORTFOLIO, $playerId);

        $company = $card['company'];
        $shares = array_fill_keys(array_column($this->players->all(), 'id'), 0);
        foreach ($this->cards->at(CardLocation::PORTFOLIO) as $owned) {
            if ($owned['company'] === $company) {
                $shares[$owned['locationArg']]++;
            }
        }
        $previous = $this->chips->owners()[$company];
        $holder = ChipHolder::next($previous, $shares);

        $chipChange = null;
        if ($holder !== $previous) {
            $this->chips->setOwner($company, $holder);
            $chipChange = ['company' => $company, 'from' => $previous, 'to' => $holder];
        }

        return ['cardId' => $cardId, 'company' => $company, 'chipChange' => $chipChange];
    }

    /**
     * Turn rule 2b: moves a card from the player's hand to the market, without chips on it.
     *
     * @param string|null $takenFromMarketCompany company of the card taken from the market this turn, null if drawn
     * @return array{cardId: int, company: string}
     * @throws TurnException
     */
    public function playToMarket(int $playerId, int $cardId, ?string $takenFromMarketCompany): array
    {
        $card = $this->cardInHand($playerId, $cardId);
        if (!MarketRules::canPutToMarket($card['company'], $takenFromMarketCompany)) {
            throw new TurnException(TurnErrors::sameCompanyAsTakenCard());
        }

        $this->cards->move($cardId, CardLocation::MARKET, 0);

        return ['cardId' => $cardId, 'company' => $card['company']];
    }

    /**
     * @return array{id: int, company: string, location: string, locationArg: int, chips: int}
     * @throws TurnException if the card is not in the player's hand
     */
    private function cardInHand(int $playerId, int $cardId): array
    {
        try {
            $card = $this->cards->get($cardId);
        } catch (\InvalidArgumentException) {
            throw new TurnException(TurnErrors::notInYourHand());
        }
        if ($card['location'] !== CardLocation::HAND || $card['locationArg'] !== $playerId) {
            throw new TurnException(TurnErrors::notInYourHand());
        }
        return $card;
    }

    /** @return string[] companies whose Anti-Monopoly chip the player holds */
    private function heldChipCompanies(int $playerId): array
    {
        return array_keys(array_filter($this->chips->owners(), fn(?int $owner) => $owner === $playerId));
    }
}
