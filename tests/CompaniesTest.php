<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\Setup;
use PHPUnit\Framework\Attributes\DataProvider;
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
    #[DataProvider('drawPileSizes')]
    public function drawPileSize_playerCount_returnsDeckMinusRemovedAndDealtCards(int $playerCount, int $expected): void
    {
        $this->assertSame($expected, Setup::drawPileSize($playerCount));
    }

    public static function drawPileSizes(): array
    {
        return [
            '3 players' => [3, 31],
            '4 players' => [4, 28],
            '5 players' => [5, 25],
            '6 players' => [6, 22],
            '7 players' => [7, 19],
        ];
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
