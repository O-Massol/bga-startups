<?php

declare(strict_types=1);

namespace Bga\Games\startups\States;

use Bga\GameFramework\StateType;
use Bga\Games\startups\Game;
use Bga\Games\startups\Rules\CardLocation;

class NextPlayer extends \Bga\GameFramework\States\GameState
{

    function __construct(
        protected Game $game,
    ) {
        parent::__construct($game,
            id: 90,
            type: StateType::GAME,
            updateGameProgression: true,
        );
    }

    /** The game ends once the last card of the draw pile has been drawn and that player's turn is over. */
    function onEnteringState(int $activePlayerId)
    {
        $this->game->giveExtraTime($activePlayerId);

        if ($this->game->cardRepository()->countAt(CardLocation::DECK) === 0) {
            return FinalScoring::class;
        }

        $this->game->activeNextPlayer();
        return PlayerTurnTake::class;
    }
}
