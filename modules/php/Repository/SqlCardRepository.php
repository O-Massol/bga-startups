<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

use Bga\Games\startups\Rules\CardLocation;

final class SqlCardRepository implements CardRepository
{
    public function __construct(private Db $db)
    {
    }

    public function insertAll(array $cards): void
    {
        $values = array_map(
            fn(array $c) => sprintf(
                "(%s, %s, %d)",
                $this->db->quote($c['company']),
                $this->db->quote($c['location']),
                $c['location_arg']
            ),
            $cards
        );
        $this->db->execute(
            "INSERT INTO `card` (`card_company`, `card_location`, `card_location_arg`) VALUES " . implode(",", $values)
        );
    }

    public function at(string $location, ?int $locationArg = null): array
    {
        $sql = "SELECT `card_id` AS `id`, `card_company` AS `company`, `card_location_arg` AS `locationArg`, `card_chips` AS `chips`"
            . " FROM `card` WHERE `card_location` = " . $this->db->quote($location);
        if ($locationArg !== null) {
            $sql .= " AND `card_location_arg` = " . $locationArg;
        }
        $rows = $this->db->collection($sql . " ORDER BY `card_location_arg`, `card_id`");
        return array_map(fn(array $r) => [
            'id' => (int) $r['id'],
            'company' => $r['company'],
            'locationArg' => (int) $r['locationArg'],
            'chips' => (int) $r['chips'],
        ], array_values($rows));
    }

    public function countAt(string $location): int
    {
        return (int) $this->db->value(
            "SELECT COUNT(*) FROM `card` WHERE `card_location` = " . $this->db->quote($location)
        );
    }

    public function countsByLocationArg(string $location): array
    {
        $rows = $this->db->collection(
            "SELECT `card_location_arg`, COUNT(*) FROM `card` WHERE `card_location` = "
                . $this->db->quote($location) . " GROUP BY `card_location_arg`",
            true
        );
        return array_map('intval', $rows);
    }

    public function get(int $cardId): array
    {
        $rows = $this->db->collection(
            "SELECT `card_id` AS `id`, `card_company` AS `company`, `card_location` AS `location`,"
                . " `card_location_arg` AS `locationArg`, `card_chips` AS `chips` FROM `card` WHERE `card_id` = " . $cardId
        );
        if ($rows === []) {
            throw new \InvalidArgumentException("Unknown card $cardId");
        }
        $r = array_values($rows)[0];
        return [
            'id' => (int) $r['id'],
            'company' => $r['company'],
            'location' => $r['location'],
            'locationArg' => (int) $r['locationArg'],
            'chips' => (int) $r['chips'],
        ];
    }

    public function move(int $cardId, string $location, int $locationArg): void
    {
        $this->db->execute(
            "UPDATE `card` SET `card_location` = " . $this->db->quote($location)
                . ", `card_location_arg` = " . $locationArg . " WHERE `card_id` = " . $cardId
        );
    }

    public function setChips(int $cardId, int $chips): void
    {
        $this->db->execute("UPDATE `card` SET `card_chips` = " . $chips . " WHERE `card_id` = " . $cardId);
    }

    public function topOfDeck(): array
    {
        $rows = $this->db->collection(
            "SELECT `card_id` AS `id`, `card_company` AS `company`, `card_location_arg` AS `locationArg`, `card_chips` AS `chips`"
                . " FROM `card` WHERE `card_location` = " . $this->db->quote(CardLocation::DECK)
                . " ORDER BY `card_location_arg`, `card_id` LIMIT 1"
        );
        if ($rows === []) {
            throw new \UnderflowException('The draw pile is empty');
        }
        $r = array_values($rows)[0];
        return [
            'id' => (int) $r['id'],
            'company' => $r['company'],
            'locationArg' => (int) $r['locationArg'],
            'chips' => (int) $r['chips'],
        ];
    }
}
