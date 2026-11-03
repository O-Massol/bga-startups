# Copilot instructions – bga-startups

Board Game Arena (BGA Studio) adaptation of Oink Games' *Startups*. The repo is the game's source folder as deployed to BGA Studio (game name: `startups`); it is currently a scaffold with no game code yet.

## Build / test / lint
No local build, test or lint tooling. Code is synced to the BGA Studio server (SFTP / VSCode, see `../reference/docs/Setting_up_BGA_Development_environment_using_VSCode.mediawiki`) and tested in the Studio by creating a table. Use the Studio "Dry-run build" to check translatable strings, and `debug_`-prefixed PHP methods (callable from the Studio debug menu) for testing.

## Reference material (outside this repo, in `../reference/`)
Read `../reference/INDEX.md` first. It indexes `../reference/docs/*.mediawiki` (offline copy of the BGA Studio docs: `Game.php`, `States/`, `Game.js`, `BgaCards`, etc.) and two community repos: `bga-sharedcode` and `bga-dev-template`. Prefer these over memory for framework API details.

## Architecture (BGA framework)
- Server: PHP 8.4 in `Game.php` plus state classes in `States/` (modern framework; `states.inc.php`, `X.action.php`, `X.view.php` are legacy). Static data in `material.inc.php`, schema in `dbmodel.sql`, stats in `stats.jsonc`, metadata in `gameinfos.jsonc`, options in `gameoptions.jsonc`.
- Client: `Game.js` (JS module) + CSS. Avoid Dojo (deprecated). Prefer official components `bga-cards`, `bga-animations`, `bga-zoom`; avoid deprecated `Deck` (PHP) and `Stock` (JS) in favour of `ItemManager` / `bga-cards`.
- Server and client communicate only through state transitions and notifications.

## Conventions / gotchas from the BGA docs
- No PHP state in globals between requests: persist everything in the DB.
- Action calls return no payload; send results to clients with `$this->bga->notify->all(...)`.
- Every state change sent via notification must also be reflected in `getAllDatas`, so a page refresh restores the same view.
- Hidden information (hands, deck, Startups' removed card) must never be sent to unauthorized players, including in `getAllDatas` and notification args.
- Wrap user-visible strings in translation functions (`clienttranslate` in PHP, `_()` in JS).
- Debug helpers must be prefixed `debug_` to appear in the Studio debug menu.
- Follow `../reference/docs/BGA_Studio_Guidelines.mediawiki` and `Pre-release_checklist.mediawiki`.
