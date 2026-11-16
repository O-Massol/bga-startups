<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/** A player attempted a move forbidden by the turn rules; the message is shown to them. */
final class TurnException extends \DomainException
{
}
