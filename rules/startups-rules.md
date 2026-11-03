# Startups (Oink Games) – rules for the BGA implementation

Sources: official rules text (https://hobbygames.ru/download/rules/startups-rules.pdf), cross-checked with https://whatsericplaying.com/2018/01/01/startups/. Items marked **[Decision]** were chosen by the project owner.

3–7 players (**[Decision]**). Goal: have the most money at the end.

## Components
- 45 company cards (shares), 6 companies (**[Decision]**; the number is the card count and the scoring order):

  | # | Company | Cards |
  |---|---|---|
  | 5 | Giraffe Beer | 5 |
  | 6 | Bowwow Games | 6 |
  | 7 | Flamingo Soft | 7 |
  | 8 | Octo Coffee | 8 |
  | 9 | Hippo Powertech | 9 |
  | 10 | Elephant Mars Travel | 10 |
- Capital chips: "1" side / "3" side.
- 6 Anti-Monopoly chips (one per company).
- Plus/minus point markers (debt and round variant).

## Setup
1. Shuffle the deck and **remove 5 random cards** face down (always 5, whatever the player count). Nobody sees them.
2. Each player gets 3 cards (hand) and 10 Capital chips, "1" side up.
3. The rest is the face-down draw pile; the **market** is the face-up area next to it (initially empty).

## Turn: do 1 then 2
1. **Take a card** from either:
   - a) The draw pile: first put 1 Capital chip (from your own money) on every market card, except cards of a company whose Anti-Monopoly chip you hold. **[Decision]** If you cannot pay for every such market card, drawing is not allowed.
   - b) The market: take the card and all chips on it. You cannot take a card of a company whose Anti-Monopoly chip you hold.
2. **Put down a card** (from your hand, now 4 cards), either:
   - a) Face up in front of you (portfolio), or
   - b) Face up in the market. If you took a card from the market this turn, you cannot put a card of the same company into the market.

## Anti-Monopoly chip
The player with the most shares (portfolio cards) of a company holds its chip. On a tie, the player who got it first keeps it until another player has strictly more. The holder (i) cannot take that company's cards from the market and (ii) does not put a chip on that company's market cards when drawing.

## End of game
The player who draws the last card of the draw pile finishes their turn, then the game ends. Every player adds their hand to their portfolio.

## Scoring
Resolve companies in ascending order, Giraffe Beer first (**[Decision]**). For each company, the player with the strictly most shares is the majority stakeholder. Every other player with shares in it pays 1 per share they own, and the stakeholder receives 3 per share (chips are handed over "3" side up). If two or more players tie for most, nobody pays or receives for that company.

**[Decision]** Simplified money model: a numeric balance per player. The payer loses 1 and the stakeholder gains 3 per share; balances may go negative (no physical +/- markers, no "can't pay with just-received 3-chips" restriction).

Winner: highest balance. Official tie-break: most "3" chips, then the player who played last. With the numeric model "3" chips aren't tracked, so unless you decide to track them (one per share received), implement the tie-break as: the player who played last.

## Not implemented
Multi-round variant (4 rounds, 1st +2 / 2nd +1 / last −1): skipped for now (**[Decision]**). Official rule for reference: replace the 5 removed cards, set up again, last-place player starts, most points after 4 rounds wins; tie-break by most +2, then +1, then last round's winner.

## Implementation notes
- Hidden: hands, draw pile order, the 5 removed cards. Public: market cards and chips on them, portfolios, chip owners, balances.
- State machine: choose source (draw / market) → put down a card → next player; when the last draw-pile card is drawn and that turn is finished → final scoring.
