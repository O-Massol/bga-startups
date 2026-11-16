<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * startups implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 */
declare(strict_types=1);

namespace Bga\Games\startups;

use Bga\GameFramework\UserException;
use Bga\Games\startups\Repository\FrameworkDb;
use Bga\Games\startups\Repository\SqlCardRepository;
use Bga\Games\startups\Repository\SqlChipRepository;
use Bga\Games\startups\Repository\SqlPlayerRepository;
use Bga\Games\startups\Service\GameStateBuilder;
use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\TurnException;
use Bga\Games\startups\Service\SetupService;
use Bga\Games\startups\Service\TurnService;
use Bga\Games\startups\States\PlayerTurnTake;

class Game extends \Bga\GameFramework\Table
{
    public array $companies;

    public function __construct()
    {
        parent::__construct();

        require_once(__DIR__ . '/../../material.inc.php');
    }

    /**
     * @return int progression between 0 and 100
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    public function upgradeTableDb($from_version)
    {
    }

    /*
     * Everything visible by the current player; called on game start and page refresh.
     */
    protected function getAllDatas(int $currentPlayerId): array
    {
        return $this->gameStateBuilder()->buildGameStateForPlayerId($currentPlayerId)->toArray();
    }

    protected function setupNewGame($players, $options = [])
    {
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        $newPlayers = [];
        foreach ($players as $player_id => $player) {
            $newPlayers[] = [
                'id' => $player_id,
                'color' => array_shift($default_colors),
                'name' => $player["player_name"],
            ];
        }
        $this->playerRepository()->createAll($newPlayers);

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        $this->setupService()->run(array_keys($players), function (array $companies): array {
            for ($i = count($companies) - 1; $i > 0; $i--) {
                $j = bga_rand(0, $i);
                [$companies[$i], $companies[$j]] = [$companies[$j], $companies[$i]];
            }
            return $companies;
        });

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();

        return PlayerTurnTake::class;
    }

    private function db(): FrameworkDb
    {
        return new FrameworkDb(
            fn(string $sql) => static::DbQuery($sql),
            fn(string $sql, bool $single) => $this->getCollectionFromDb($sql, $single),
            fn(string $sql) => static::getUniqueValueFromDB($sql),
            fn(string $value) => "'" . static::escapeStringForDB($value) . "'",
        );
    }

    public function playerRepository(): SqlPlayerRepository
    {
        return new SqlPlayerRepository($this->db());
    }

    public function cardRepository(): SqlCardRepository
    {
        return new SqlCardRepository($this->db());
    }

    public function turnService(): TurnService
    {
        return new TurnService($this->cardRepository(), new SqlChipRepository($this->db()), $this->playerRepository());
    }

    public function companyName(string $companyId): string
    {
        return $this->companies[$companyId]['name'];
    }

    /**
     * Runs a player move and shows a refused move to the player as an error.
     *
     * @template T
     * @param callable(): T $move
     * @return T
     * @throws UserException
     */
    public function refusingIllegalMoves(callable $move): mixed
    {
        try {
            return $move();
        } catch (TurnException $e) {
            throw new UserException($e->getMessage());
        }
    }

    private function setupService(): SetupService
    {
        return new SetupService(new SqlCardRepository($this->db()), new SqlChipRepository($this->db()));
    }

    private function gameStateBuilder(): GameStateBuilder
    {
        return new GameStateBuilder(
            $this->playerRepository(),
            new SqlCardRepository($this->db()),
            new SqlChipRepository($this->db()),
            $this->companies
        );
    }

    public function debug_goToState(int $state = 3) {
        $this->gamestate->jumpToState($state);
    }

    /** Keeps only the next $cards cards in the draw pile (the others are set aside) so the game ends soon. */
    public function debug_nearEndDeck(int $cards = 2)
    {
        $this->db()->execute(
            "UPDATE `card` SET `card_location` = 'removed' WHERE `card_location` = 'deck' AND `card_id` NOT IN ("
                . "SELECT `card_id` FROM (SELECT `card_id` FROM `card` WHERE `card_location` = 'deck'"
                . " ORDER BY `card_location_arg`, `card_id` LIMIT " . $cards . ") AS `top`)"
        );
    }

    public function debug_giveMoney(int $playerId, int $amount = 5)
    {
        $this->playerRepository()->addMoney($playerId, $amount);
    }

    /** Moves a card of the given company from the draw pile or the removed cards into a player's hand. */
    public function debug_giveCard(int $playerId, string $company = 'giraffe_beer')
    {
        $cardId = $this->db()->value(
            "SELECT `card_id` FROM `card` WHERE `card_company` = " . $this->db()->quote($company)
                . " AND `card_location` IN ('deck', 'removed') LIMIT 1"
        );
        if ($cardId === null) {
            throw new UserException("No spare card of $company");
        }
        $this->cardRepository()->move((int) $cardId, CardLocation::HAND, $playerId);
    }
}
