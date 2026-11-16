<?php

declare(strict_types=1);

namespace Bga\Games\startups\Rules;

/** Anti-Monopoly chip rule: most shares holds the chip; ties keep the earlier holder. */
final class ChipHolder
{
    /**
     * @param int|null $currentHolder player id holding the chip, null if unowned
     * @param array<int, int> $sharesByPlayer portfolio cards of the company, keyed by player id
     * @return int|null the holder after re-evaluation
     */
    public static function next(?int $currentHolder, array $sharesByPlayer): ?int
    {
        $holder = $currentHolder;
        $best = $currentHolder === null ? 0 : ($sharesByPlayer[$currentHolder] ?? 0);
        foreach ($sharesByPlayer as $playerId => $shares) {
            if ($shares > $best) {
                [$holder, $best] = [$playerId, $shares];
            }
        }
        return $holder;
    }
}
