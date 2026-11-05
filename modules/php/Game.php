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
        $result = [];
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` AS `id`, `player_score` AS `score` FROM `player`"
        );

        return $result;
    }

    protected function setupNewGame($players, $options = [])
    {
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            $query_values[] = vsprintf("(%s, '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                addslashes($player["player_name"]),
            ]);
        }

        static::DbQuery(
            sprintf(
                "INSERT INTO `player` (`player_id`, `player_color`, `player_name`) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // TODO: Setup the initial game situation here.

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();

        return PlayerTurn::class;
    }

    public function debug_goToState(int $state = 3) {
        $this->gamestate->jumpToState($state);
    }
}
