<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\Setup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CompaniesTest extends TestCase
{
    #[Test]
    public function cardCounts_all_areSixCompaniesInScoringOrderWithExpectedCounts(): void
    {
        $this->assertSame(
            [
                'giraffe_beer' => 5,
                'bowwow_games' => 6,
                'flamingo_soft' => 7,
                'octo_coffee' => 8,
                'hippo_powertech' => 9,
                'elephant_mars_travel' => 10,
            ],
            Companies::CARD_COUNTS
        );
    }

    #[Test]
    public function totalCards_allCompanies_returns45(): void
    {
        $this->assertSame(45, Companies::totalCards());
    }

    #[Test]
    public function totalCards_maxPlayersAndRemovedCards_leavesNonEmptyDrawPile(): void
    {
        $dealt = Setup::MAX_PLAYERS * Setup::HAND_SIZE;
        $this->assertGreaterThan(0, Companies::totalCards() - Setup::CARDS_REMOVED - $dealt);
    }

    #[Test]
    public function materialCompanies_allCompanyIds_haveMatchingNonEmptyNames(): void
    {
        $game = new class {
            public array $companies;

            public function load(): void
            {
                require dirname(__DIR__) . "/material.inc.php";
            }
        };
        $game->load();

        $this->assertSame(Companies::ids(), array_keys($game->companies));
        foreach ($game->companies as $company) {
            $this->assertNotSame('', $company['name']);
        }
    }
}
