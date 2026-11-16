<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\ChipHolder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChipHolderTest extends TestCase
{
    #[Test]
    public function next_unownedAndNobodyHasShares_staysUnowned(): void
    {
        $this->assertNull(ChipHolder::next(null, [1 => 0, 2 => 0]));
    }

    #[Test]
    public function next_unownedAndOnePlayerHasShares_thatPlayerTakesIt(): void
    {
        $this->assertSame(2, ChipHolder::next(null, [1 => 0, 2 => 1]));
    }

    #[Test]
    public function next_holderKeepsLead_staysHolder(): void
    {
        $this->assertSame(1, ChipHolder::next(1, [1 => 3, 2 => 1]));
    }

    #[Test]
    public function next_otherPlayerStrictlyMore_takesIt(): void
    {
        $this->assertSame(2, ChipHolder::next(1, [1 => 2, 2 => 3]));
    }

    #[Test]
    public function next_otherPlayerTiesHolder_holderKeepsIt(): void
    {
        $this->assertSame(1, ChipHolder::next(1, [1 => 2, 2 => 2]));
    }

    #[Test]
    public function next_holderTiedWithSeveralOthers_holderKeepsIt(): void
    {
        $this->assertSame(3, ChipHolder::next(3, [1 => 2, 2 => 2, 3 => 2]));
    }

    #[Test]
    public function next_severalPlayersBeatHolder_theOneWithMostSharesTakesIt(): void
    {
        $this->assertSame(3, ChipHolder::next(1, [1 => 1, 2 => 2, 3 => 4]));
    }

    #[Test]
    public function next_playerMissingFromShares_countsAsZero(): void
    {
        $this->assertSame(1, ChipHolder::next(1, [2 => 0]));
    }
}
