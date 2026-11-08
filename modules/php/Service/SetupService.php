<?php

declare(strict_types=1);

namespace Bga\Games\startups\Service;

use Bga\Games\startups\Repository\CardRepository;
use Bga\Games\startups\Repository\ChipRepository;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\Setup;

/** Setup rules 1-3: creates the deck, deals it and creates the Anti-Monopoly chips. */
final class SetupService
{
    public function __construct(
        private CardRepository $cards,
        private ChipRepository $chips,
    ) {
    }

    /**
     * @param int[] $playerIds in turn order
     * @param callable(string[]): string[] $shuffle returns the given company ids in random order
     */
    public function run(array $playerIds, callable $shuffle): void
    {
        $this->cards->insertAll(Setup::deal($shuffle(Companies::allCards()), $playerIds));
        $this->chips->createAll(Companies::ids());
    }
}
