# Startups – Board Game Arena implementation

Implementation of Oink Games' *Startups* for BGA Studio.

## Studio setup (plan step 0)

1. Create a BGA developer account and request Studio access (https://studio.boardgamearena.com), then add your public SSH key in the Studio control panel. The dedicated key pair lives in `../bga-ssh/` (`id_rsa`, `id_rsa.pub`; never commit or share the private key). Paste the content of `../bga-ssh/id_rsa.pub` into Studio.
2. In Studio, "Create a new game" named `startups` from the `emptygame` template.
3. Sync without VSCode: `scripts/sftp-sync.sh ls|pull|push` (SFTP, key `../bga-ssh/id_rsa`, user `omassol`; override with `BGA_SSH_KEY`, `BGA_USER`, `BGA_PROJECT`). `push` uploads game files only (docs, rules, scripts and dotfiles are excluded).
4. `scripts/sftp-sync.sh pull` pulled the generated skeleton (done; baseline committed). VSCode alternative: copy `.vscode/sftp.json.example` to `.vscode/sftp.json` and use `SFTP: Download Project`.
5. Create a 3-player test table in Studio; it must open from these files.

`delete` is `false` in the SFTP config so a sync cannot wipe the remote project by mistake.

See [docs/REFERENCES.md](docs/REFERENCES.md) for documentation and reference repositories.
