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

use Bga\Games\startups\Repository\FrameworkDb;
use Bga\Games\startups\Repository\SqlCardRepository;
use Bga\Games\startups\Repository\SqlChipRepository;
use Bga\Games\startups\Repository\SqlPlayerRepository;
use Bga\Games\startups\Service\GameStateBuilder;
use Bga\Games\startups\Service\SetupService;
use Bga\Games\startups\States\PlayerTurn;

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
        return $this->gameStateBuilder()->build($currentPlayerId) + ['companies' => $this->companies];
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

        return PlayerTurn::class;
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

    private function playerRepository(): SqlPlayerRepository
    {
        return new SqlPlayerRepository($this->db());
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
            new SqlChipRepository($this->db())
        );
    }

    public function debug_goToState(int $state = 3) {
        $this->gamestate->jumpToState($state);
    }
}
