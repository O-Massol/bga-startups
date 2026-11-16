<?php

declare(strict_types=1);

namespace Bga\Games\startups\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\startups\Game;
use Bga\Games\startups\Rules\ZombieChoice;

/** Turn rule 2: put a card from the hand in the portfolio or in the market. */
class PlayerTurnPlay extends GameState
{
    function __construct(
        protected Game $game,
    ) {
        parent::__construct($game,
            id: 11,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must put a card down'),
            descriptionMyTurn: clienttranslate('${you} must put a card down, in your portfolio or in the market'),
        );
    }

    public function getArgs(): array
    {
        return ['forbiddenMarketCompany' => $this->bga->globals->get(PlayerTurnTake::TOOK_FROM_MARKET_COMPANY)];
    }

    #[PossibleAction]
    public function actPlayToPortfolio(int $cardId, int $activePlayerId)
    {
        $played = $this->game->refusingIllegalMoves(fn() => $this->game->turnService()->playToPortfolio($activePlayerId, $cardId));

        $this->bga->notify->all(
            'cardPlayedToPortfolio',
            clienttranslate('${player_name} adds a ${company_name} card to their portfolio'),
            [
                'i18n' => ['company_name'],
                'player_id' => $activePlayerId,
                'player_name' => $this->game->getPlayerNameById($activePlayerId),
                'card' => ['id' => $played['cardId'], 'company' => $played['company']],
                'company_name' => $this->game->companyName($played['company']),
            ]
        );
        if ($played['chipChange'] !== null) {
            $this->bga->notify->all(
                'antiMonopolyChipMoved',
                clienttranslate('${player_name} takes the Anti-Monopoly chip of ${company_name}'),
                [
                    'i18n' => ['company_name'],
                    'player_id' => $activePlayerId,
                    'player_name' => $this->game->getPlayerNameById($activePlayerId),
                    'company' => $played['chipChange']['company'],
                    'company_name' => $this->game->companyName($played['chipChange']['company']),
                    'from' => $played['chipChange']['from'],
                    'to' => $played['chipChange']['to'],
                ]
            );
        }

        return NextPlayer::class;
    }

    #[PossibleAction]
    public function actPlayToMarket(int $cardId, int $activePlayerId)
    {
        $takenCompany = $this->bga->globals->get(PlayerTurnTake::TOOK_FROM_MARKET_COMPANY);
        $played = $this->game->refusingIllegalMoves(
            fn() => $this->game->turnService()->playToMarket($activePlayerId, $cardId, $takenCompany)
        );

        $this->bga->notify->all(
            'cardPlayedToMarket',
            clienttranslate('${player_name} puts a ${company_name} card in the market'),
            [
                'i18n' => ['company_name'],
                'player_id' => $activePlayerId,
                'player_name' => $this->game->getPlayerNameById($activePlayerId),
                'card' => ['id' => $played['cardId'], 'company' => $played['company']],
                'company_name' => $this->game->companyName($played['company']),
            ]
        );

        return NextPlayer::class;
    }

    function zombie(int $playerId)
    {
        $cardId = ZombieChoice::cardToPlay($this->game->turnService()->handCardIds($playerId));
        return $this->actPlayToPortfolio($cardId, $playerId);
    }
}
