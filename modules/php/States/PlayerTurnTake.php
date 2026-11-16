<?php

declare(strict_types=1);

namespace Bga\Games\startups\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\startups\Game;
use Bga\Games\startups\Rules\CardLocation;

/** Turn rule 1: take a card, from the draw pile or from the market. */
class PlayerTurnTake extends GameState
{
    /** Global: company of the card taken from the market this turn, null if the player drew. */
    public const TOOK_FROM_MARKET_COMPANY = 'tookFromMarketCompany';

    function __construct(
        protected Game $game,
    ) {
        parent::__construct($game,
            id: 10,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must take a card from the draw pile or from the market'),
            descriptionMyTurn: clienttranslate('${you} must take a card from the draw pile or from the market'),
        );
    }

    public function getArgs(int $activePlayerId): array
    {
        return $this->game->turnService()->takeOptions($activePlayerId);
    }

    #[PossibleAction]
    public function actDrawFromDeck(int $activePlayerId)
    {
        $drawn = $this->game->refusingIllegalMoves(fn() => $this->game->turnService()->drawFromDeck($activePlayerId));
        $this->bga->globals->set(self::TOOK_FROM_MARKET_COMPANY, null);

        $this->bga->notify->all(
            'cardDrawn',
            $drawn['cost'] > 0
                ? clienttranslate('${player_name} draws a card and puts ${cost} chip(s) on the market')
                : clienttranslate('${player_name} draws a card'),
            [
                'player_id' => $activePlayerId,
                'player_name' => $this->game->getPlayerNameById($activePlayerId),
                'cost' => $drawn['cost'],
                'chippedCardIds' => $drawn['chippedCardIds'],
                'money' => $this->game->playerRepository()->money($activePlayerId),
                'deckCount' => $this->game->cardRepository()->countAt(CardLocation::DECK),
            ]
        );
        // Only the drawer learns which card it is.
        $this->bga->notify->player($activePlayerId, 'cardDrawnPrivate', '', [
            'card' => ['id' => $drawn['cardId'], 'company' => $drawn['company']],
        ]);

        return PlayerTurnPlay::class;
    }

    #[PossibleAction]
    public function actTakeFromMarket(int $cardId, int $activePlayerId)
    {
        $taken = $this->game->refusingIllegalMoves(fn() => $this->game->turnService()->takeFromMarket($activePlayerId, $cardId));
        $this->bga->globals->set(self::TOOK_FROM_MARKET_COMPANY, $taken['company']);

        $this->bga->notify->all(
            'cardTakenFromMarket',
            $taken['chips'] > 0
                ? clienttranslate('${player_name} takes a ${company_name} card and ${chips} chip(s) from the market')
                : clienttranslate('${player_name} takes a ${company_name} card from the market'),
            [
                'i18n' => ['company_name'],
                'player_id' => $activePlayerId,
                'player_name' => $this->game->getPlayerNameById($activePlayerId),
                'card' => ['id' => $taken['cardId'], 'company' => $taken['company']],
                'company_name' => $this->game->companyName($taken['company']),
                'chips' => $taken['chips'],
                'money' => $this->game->playerRepository()->money($activePlayerId),
            ]
        );

        return PlayerTurnPlay::class;
    }

    function zombie(int $playerId)
    {
        $cardId = $this->game->turnService()->zombieMarketCardToTake($playerId);
        return $cardId === null ? $this->actDrawFromDeck($playerId) : $this->actTakeFromMarket($cardId, $playerId);
    }
}
