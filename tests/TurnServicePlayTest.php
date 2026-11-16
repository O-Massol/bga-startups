<?php

declare(strict_types=1);

use Bga\Games\startups\Rules\CardLocation;
use Bga\Games\startups\Rules\Companies;
use Bga\Games\startups\Rules\TurnErrors;
use Bga\Games\startups\Tests\Support\TurnServiceTestCase;
use PHPUnit\Framework\Attributes\Test;

/** Turn rule 2: put a card down, in the portfolio or in the market. */
final class TurnServicePlayTest extends TurnServiceTestCase
{
    // --- playToPortfolio ---

    #[Test]
    public function playToPortfolio_cardInHand_movesToPortfolioAndMoneyIsUnchanged(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $result = $this->service->playToPortfolio(self::ME, $card);

        $this->assertSame($card, $result['cardId']);
        $this->assertSame(Companies::OCTO_COFFEE, $result['company']);
        $this->assertSame([$card], array_column($this->cards->at(CardLocation::PORTFOLIO, self::ME), 'id'));
        $this->assertSame([], $this->cards->at(CardLocation::HAND));
        $this->assertSame(10, $this->players->money(self::ME));
    }

    #[Test]
    public function playToPortfolio_cardInMarket_isRefused(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::MARKET);

        $this->assertRefused(TurnErrors::notInYourHand(), fn() => $this->service->playToPortfolio(self::ME, $card));
    }

    #[Test]
    public function playToPortfolio_cardInAnotherPlayersHand_isRefused(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::OTHER);

        $this->assertRefused(TurnErrors::notInYourHand(), fn() => $this->service->playToPortfolio(self::ME, $card));
        $this->assertSame(CardLocation::HAND, $this->cards->get($card)['location']);
    }

    #[Test]
    public function playToPortfolio_unknownCard_isRefused(): void
    {
        $this->assertRefused(TurnErrors::notInYourHand(), fn() => $this->service->playToPortfolio(self::ME, 99));
    }

    #[Test]
    public function playToPortfolio_firstShareOfAnUnownedCompany_takesItsChip(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $result = $this->service->playToPortfolio(self::ME, $card);

        $this->assertSame(['company' => Companies::OCTO_COFFEE, 'from' => null, 'to' => self::ME], $result['chipChange']);
        $this->assertSame(self::ME, $this->chips->owners()[Companies::OCTO_COFFEE]);
    }

    #[Test]
    public function playToPortfolio_strictlyMoreSharesThanTheHolder_takesTheChipFromThem(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::PORTFOLIO, self::OTHER);
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::PORTFOLIO, self::ME);
        $this->chips->setOwner(Companies::OCTO_COFFEE, self::OTHER);
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $result = $this->service->playToPortfolio(self::ME, $card);

        $this->assertSame(['company' => Companies::OCTO_COFFEE, 'from' => self::OTHER, 'to' => self::ME], $result['chipChange']);
        $this->assertSame(self::ME, $this->chips->owners()[Companies::OCTO_COFFEE]);
    }

    #[Test]
    public function playToPortfolio_tieWithTheHolder_holderKeepsTheChip(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::PORTFOLIO, self::OTHER);
        $this->chips->setOwner(Companies::OCTO_COFFEE, self::OTHER);
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $result = $this->service->playToPortfolio(self::ME, $card);

        $this->assertNull($result['chipChange']);
        $this->assertSame(self::OTHER, $this->chips->owners()[Companies::OCTO_COFFEE]);
    }

    #[Test]
    public function playToPortfolio_holderAddsAShare_chipDoesNotChange(): void
    {
        $this->addCard(Companies::OCTO_COFFEE, CardLocation::PORTFOLIO, self::ME);
        $this->chips->setOwner(Companies::OCTO_COFFEE, self::ME);
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $this->assertNull($this->service->playToPortfolio(self::ME, $card)['chipChange']);
        $this->assertSame(self::ME, $this->chips->owners()[Companies::OCTO_COFFEE]);
    }

    #[Test]
    public function playToPortfolio_otherCompaniesChips_areNotTouched(): void
    {
        $this->chips->setOwner(Companies::GIRAFFE_BEER, self::OTHER);
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $this->service->playToPortfolio(self::ME, $card);

        $this->assertSame(self::OTHER, $this->chips->owners()[Companies::GIRAFFE_BEER]);
        $this->assertNull($this->chips->owners()[Companies::BOWWOW_GAMES]);
    }

    // --- playToMarket ---

    #[Test]
    public function playToMarket_cardInHand_entersTheMarketWithoutChips(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $result = $this->service->playToMarket(self::ME, $card, null);

        $this->assertSame(['cardId' => $card, 'company' => Companies::OCTO_COFFEE], $result);
        $this->assertSame([$card], array_column($this->cards->at(CardLocation::MARKET), 'id'));
        $this->assertSame([0], array_column($this->cards->at(CardLocation::MARKET), 'chips'));
        $this->assertSame(10, $this->players->money(self::ME));
    }

    #[Test]
    public function playToMarket_otherCompanyThanTheTakenCardCompany_isAllowed(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $this->service->playToMarket(self::ME, $card, Companies::GIRAFFE_BEER);

        $this->assertSame(CardLocation::MARKET, $this->cards->get($card)['location']);
    }

    #[Test]
    public function playToMarket_sameCompanyAsTheTakenCardCompany_isRefusedAndCardStaysInHand(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::ME);

        $this->assertRefused(
            TurnErrors::sameCompanyAsTakenCard(),
            fn() => $this->service->playToMarket(self::ME, $card, Companies::OCTO_COFFEE)
        );

        $this->assertSame(CardLocation::HAND, $this->cards->get($card)['location']);
    }

    #[Test]
    public function playToMarket_cardInAnotherPlayersHand_isRefused(): void
    {
        $card = $this->addCard(Companies::OCTO_COFFEE, CardLocation::HAND, self::OTHER);

        $this->assertRefused(TurnErrors::notInYourHand(), fn() => $this->service->playToMarket(self::ME, $card, null));
    }

    #[Test]
    public function playToMarket_unknownCard_isRefused(): void
    {
        $this->assertRefused(TurnErrors::notInYourHand(), fn() => $this->service->playToMarket(self::ME, 99, null));
    }
}
