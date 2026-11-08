# Implementation plan – Startups on BGA

Rules: `rules/startups-rules.md`. Framework docs: `../reference/INDEX.md`.
Use the modern framework (`Game.php`, `States/`, `Game.js`), not the legacy `states.inc.php` / `action.php` / `view.php`.
Each iteration ends with a playable, testable Studio build and a commit.

## 0. Environment
- [x] Get a BGA Studio account and create the project `startups`; start from the `emptygame` template.
- [x] Set up SFTP/VSCode sync (`../reference/docs/Setting_up_BGA_Development_environment_using_VSCode.mediawiki`, `../reference/bga-dev-template`) so the repo root maps to the Studio project folder.
- [x] Copy the generated skeleton into this repo; commit as baseline.
- Done when: a 3-player test table opens in Studio from this repo's files. (verified: table #990994)

## 1. Metadata and static data
- [x] `gameinfos.jsonc`: name, players 3–7, no options, no turn-order tweaks, tie-break info.
- [x] `material.inc.php`: 6 companies (id, name, card count 5–10), constants.
- [x] `dbmodel.sql`: tables for cards (id, company, location, location_arg), market chips, anti-monopoly chip owner per company; player balance via the standard `player.player_score` or a dedicated column.
- [x] `stats.jsonc`: a few stats (e.g. cards taken from market, chips paid out, final majorities held).
- Done when: the table is created without SQL errors.

## 2. Game setup (`setupNewGame`)
- [ ] Build the 45-card deck, shuffle, remove 5 (location `removed`), deal 3 per player, give 10 chips each.
- [ ] Initial `getAllDatas`: own hand (private), other players' hand counts, market (empty), portfolios, chip owners, balances, deck size.
- [ ] Minimal `Game.js` that renders this state (plain DOM first).
- Done when: starting a table shows each player's own 3 cards and 10 money; others' hands are hidden.

## 3. Turn state machine (server)
States in `States/`:
- `PlayerTurnTake` (active player): actions `actDrawFromDeck`, `actTakeFromMarket(cardId)`.
- `PlayerTurnPlay` (active player): actions `actPlayToPortfolio(cardId)`, `actPlayToMarket(cardId)`.
- `NextPlayer` (game): updates anti-monopoly chips, checks end condition, advances the player.
- `FinalScoring` (game) → `EndScore`.
Rules to enforce in the server, with the exact `UserException` message when violated:
- Draw: cost = 1 chip per market card whose company chip you don't hold; refuse if you can't pay for all.
- Market take: refuse if you hold that company's chip; gain the chips on the card.
- Market put-back: refuse the same company as the card just taken from the market.
- Chip owner: strictly more shares than the current holder to take it; ties keep the earlier holder.
- End: when the last draw-pile card is drawn, the game finishes after that player's `PlayerTurnPlay`.
- [ ] Implement `zombie()` (draw if possible, else take any allowed market card, then play to portfolio) so abandoned games progress.
- [ ] Add `debug_` helpers: set up a near-end deck, give a player cards or money.
- Done when: a full game can be played through Studio's debug controls with plain buttons, reaching the end state.

## 4. Scoring
- [ ] `FinalScoring`: add each hand to its portfolio, then resolve companies 5→10: strict majority gets +3 per share of every other player holding shares in it; each payer −1 per share; no payout on ties. Send one notification per company for animation.
- [ ] Update `player_score`, and tie-break `player_score_aux` = played last (see `rules/startups-rules.md`).
- Done when: debug-seeded final positions give the expected balances (cover tie, 3 players with 2 equal majorities, negative balances).

## 5. Notifications and replay safety
- [ ] One notification type per event (draw, take, play, market chip placement, chip moved, scoring step), private variants for hand info (`notify->player`).
- [ ] Every notification's effect is also present in `getAllDatas` (refresh in mid-game must restore the same view).
- [ ] Check game replay and "undo"/reload behaviour (`../reference/docs/Game_replay.mediawiki`).
- Done when: refreshing the browser at any point reproduces the same UI.

## 6. Real UI
- [ ] Layout: market area with chips on cards, draw pile with count, own hand, per-player portfolios grouped by company, company panel with chip holder.
- [ ] Use `bga-cards` (`../reference/docs/BgaCards.mediawiki`) for hands/market/portfolios and `bga-animations` for moves; `Counter` for balances.
- [ ] Highlight legal actions client-side only as a hint (server stays authoritative).
- [ ] Card art: Oink's artwork is copyrighted. Use original/placeholder art (sprite sheet of company icons + number) until a licence/permission is sorted out.
- Done when: a full game is comfortably playable on desktop.

## 7. Polish
- [ ] Mobile layout and zoom (`BgaZoom`), game log messages with translatable strings (`clienttranslate`, `_()`), game progression (`getGameProgression` from draw-pile size), player-panel counters.
- [ ] End-of-game score sheet (`BgaScoreSheet`) showing per-company payouts.
- [ ] Turn-based/real-time time settings and stats.
- Done when: the Pre-release checklist (`../reference/docs/Pre-release_checklist.mediawiki`) passes for everything not blocked by licensing.

## 8. Later / optional
- [ ] Multi-round variant as a game option (skipped for now).
- [ ] Track "3" chips to implement the official tie-break (see rules file).
- [ ] Bots (`../reference/docs/Bots_and_Artificial_Intelligence.mediawiki`).
- [ ] Publisher licence and submission (`BGA_game_Lifecycle`, `BGA_Game_licenses`).

## Automated tests (PHPUnit) – cross-cutting, start with sections 3–4
Approach: keep rules logic in pure PHP classes (`modules/php/Rules/`, no `Game`/DB dependency): draw cost, market take/put-back legality, chip-holder majority, final scoring. `Game.php` and `States/` stay thin (load data, call rules, persist, notify).
- [ ] Install PHPUnit via composer **outside the repo** (e.g. `~/php-composer`, see `../reference/docs/Setting_up_BGA_Development_environment_using_VSCode.mediawiki`, "Unit testing" and "Testing by developer"), and add that path to `intelephense.environment.includePaths`.
- [ ] Add `tests/` and `phpunit.xml` in the repo; first tests: scoring (tie, 3 players with 2 equal majorities, negative balances), draw cost, chip-holder tie keeps earlier holder.
- [ ] Add `tests`, `phpunit.xml`, `composer.json`, `composer.lock`, `vendor` to the `ignore` list in `.vscode/sftp.json.example` (and local `sftp.json`) so they are not uploaded to Studio / shipped on release.
Limitations:
- Not unit-testable cheaply: state transitions, `getAllDatas`, notifications, replay safety, DB queries. Cover them with Studio `debug_` helpers and the test checklist below.
- The SFTP `ignore` list only affects the VSCode SFTP extension. Any other sync (rsync, BGA release process) must exclude the same files.
- Rules classes must stay deployable (they are runtime code); only the tests are excluded.
- No official BGA test harness; no browser end-to-end tests planned (fragile: login, iframes, table creation).
- JS tests (Vitest/Jest) only for pure helpers, low priority.

## Test checklist (run each iteration)
- 3, 5 and 7 player tables; refresh mid-turn; zombie/quit a player; replay from a move.
- Edge cases: cannot afford draw; holder tries to take its company from the market; put back same company; tie for majority at scoring; last card drawn by the last seat.
