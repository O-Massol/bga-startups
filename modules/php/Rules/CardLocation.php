<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

final class CardLocation
{
    /** Face-down draw pile. */
    public const DECK = 'deck';
    /** Private hand of a player (location_arg = player id). */
    public const HAND = 'hand';
    /** Face-up market next to the draw pile; chips sit on its cards. */
    public const MARKET = 'market';
    /** Face-up cards in front of a player (location_arg = player id); counted for majorities. */
    public const PORTFOLIO = 'portfolio';
    /** The 5 cards removed at setup; hidden from everyone. */
    public const REMOVED = 'removed';
}
