<?php

declare(strict_types=1);

namespace Bga\Games\startups\Service;

use Bga\Games\startups\Repository\CardRepository;
use Bga\Games\startups\Repository\ChipRepository;
use Bga\Games\startups\Repository\PlayerRepository;
use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\Companies;

/**
 * Builds the getAllDatas payload. The only place deciding what a player may see:
 * never the removed cards, the draw pile order or other players' hands.
 */
final class GameStateBuilder
{
    public function __construct(
        private PlayerRepository $players,
        private CardRepository $cards,
        private ChipRepository $chips,
    ) {
    }

    public function build(int $currentPlayerId): array
    {
        $players = $this->players->all();

        $portfolios = [];
        foreach (array_keys($players) as $playerId) {
            $portfolios[$playerId] = $this->cards->at(CardLocation::PORTFOLIO, $playerId);
        }

        return [
            'players' => $players,
            'hand' => $this->cards->at(CardLocation::HAND, $currentPlayerId),
            'handCounts' => $this->cards->countsByLocationArg(CardLocation::HAND),
            'market' => $this->cards->at(CardLocation::MARKET),
            'portfolios' => $portfolios,
            'chipOwners' => $this->chips->owners(),
            'deckCount' => $this->cards->countAt(CardLocation::DECK),
            'companyCardCounts' => Companies::CARD_COUNTS,
        ];
    }
}
