# Startups – Board Game Arena implementation

Implementation of Oink Games' *Startups* for BGA Studio.

## Studio setup (plan step 0)

1. Create a BGA developer account and request Studio access (https://studio.boardgamearena.com), then add your public SSH key in the Studio control panel. The dedicated key pair lives in `../bga-ssh/` (`id_rsa`, `id_rsa.pub`; never commit or share the private key). Paste the content of `../bga-ssh/id_rsa.pub` into Studio.
2. In Studio, "Create a new game" named `startups` from the `emptygame` template.
3. Install VSCode + the "SFTP" extension (Natizyskunk). Copy `.vscode/sftp.json.example` to `.vscode/sftp.json` (git-ignored) and set your username (the key path already points to `../bga-ssh/id_rsa`; adjust if your checkout is elsewhere).
4. Run `SFTP: Download Project` once to pull the generated skeleton into this folder, then commit it as the baseline.
5. Create a 3-player test table in Studio; it must open from these files.

`delete` is `false` in the SFTP config so a sync cannot wipe the remote project by mistake.

See [docs/REFERENCES.md](docs/REFERENCES.md) for documentation and reference repositories.
