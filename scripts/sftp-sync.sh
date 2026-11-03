#!/usr/bin/env bash
# Sync this repo with the BGA Studio project over SFTP (no VSCode needed).
# Usage: scripts/sftp-sync.sh ls|pull|push
# Auth: SSH key ../bga-ssh/id_rsa (outside the repo). Override with BGA_SSH_KEY / BGA_USER.
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
KEY="${BGA_SSH_KEY:-$REPO/../bga-ssh/id_rsa}"
USER_="${BGA_USER:-omassol}"
HOST=1.studio.boardgamearena.com
PORT=2022
PROJECT="${BGA_PROJECT:-startups}"

SFTP=(sftp -b - -P "$PORT" -i "$KEY" -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new "$USER_@$HOST")

# Paths not uploaded to Studio
EXCLUDES=(.git .github .vscode scripts docs rules plan.md README.md .gitignore)

case "${1:-}" in
  ls)
    printf 'ls -la %s\n' "$PROJECT" | "${SFTP[@]}"
    ;;
  pull)
    printf 'lcd %s\nget -r %s/* .\n' "$REPO" "$PROJECT" | "${SFTP[@]}"
    ;;
  push)
    cd "$REPO"
    batch="lcd $REPO\ncd $PROJECT\n"
    for f in * .[!.]*; do
      [[ -e "$f" ]] || continue
      skip=0
      for e in "${EXCLUDES[@]}"; do [[ "$f" == "$e" ]] && skip=1; done
      [[ $skip -eq 1 ]] && continue
      [[ -d "$f" ]] && batch+="-mkdir $f\n"
      batch+="put -r $f\n"
    done
    printf "$batch" | "${SFTP[@]}"
    ;;
  *)
    echo "Usage: $0 ls|pull|push" >&2
    exit 1
    ;;
esac
