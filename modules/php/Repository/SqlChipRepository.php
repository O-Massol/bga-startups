<?php

declare(strict_types=1);

namespace Bga\Games\startups\Repository;

final class SqlChipRepository implements ChipRepository
{
    public function __construct(private Db $db)
    {
    }

    public function createAll(array $companyIds): void
    {
        $values = array_map(fn(string $id) => "(" . $this->db->quote($id) . ")", $companyIds);
        $this->db->execute("INSERT INTO `company_chip` (`company_id`) VALUES " . implode(",", $values));
    }

    public function owners(): array
    {
        $rows = $this->db->collection("SELECT `company_id`, `owner_id` FROM `company_chip`", true);
        return array_map(fn($owner) => $owner === null ? null : (int) $owner, $rows);
    }
}
