<?php

declare(strict_types=1);

namespace Bga\Games\startups\States;

use Bga\GameFramework\StateType;

/** Placeholder: the real scoring (hands into portfolios, company by company payouts) is section 4 of plan.md. */
class FinalScoring extends \Bga\GameFramework\States\GameState
{
    function __construct(
        protected \Bga\Games\startups\Game $game,
    ) {
        parent::__construct($game,
            id: 91,
            type: StateType::GAME,
        );
    }

    public function onEnteringState()
    {
        return EndScore::class;
    }
}
