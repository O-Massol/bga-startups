<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

final class SqlPlayerRepository implements PlayerRepository
{
    public function __construct(private Db $db)
    {
    }

    public function createAll(array $players): void
    {
        $values = array_map(
            fn(array $p) => sprintf(
                "(%d, %s, %s)",
                $p['id'],
                $this->db->quote($p['color']),
                $this->db->quote($p['name'])
            ),
            $players
        );
        $this->db->execute(
            "INSERT INTO `player` (`player_id`, `player_color`, `player_name`) VALUES " . implode(",", $values)
        );
    }

    public function all(): array
    {
        $rows = $this->db->collection(
            "SELECT `player_id` AS `id`, `player_score` AS `score`, `player_money` AS `money` FROM `player`"
        );
        return array_map(fn(array $r) => [
            'id' => (int) $r['id'],
            'score' => (int) $r['score'],
            'money' => (int) $r['money'],
        ], $rows);
    }
}
