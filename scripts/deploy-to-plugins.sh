#!/bin/bash
# Manuāls deploy no repozitorija saknes (SSH vai cPanel Terminal).
# Lietošana: cd ~/repositories/EFPIC && bash scripts/deploy-to-plugins.sh

set -euo pipefail

PLUGINPATH="${PLUGINPATH:-/home2/trioitlv/edgarsfoto.lv/wp-content/plugins}"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

cd "$REPO_ROOT"

if [ ! -d "efpic" ] || [ ! -d "efpic-pro" ]; then
  echo "Kļūda: efpic/ vai efpic-pro/ nav repozitorija saknē ($REPO_ROOT)" >&2
  exit 1
fi

if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete ./efpic/ "$PLUGINPATH/efpic/"
  rsync -a --delete ./efpic-pro/ "$PLUGINPATH/efpic-pro/"
else
  rm -rf "$PLUGINPATH/efpic" "$PLUGINPATH/efpic-pro"
  cp -a ./efpic "$PLUGINPATH/"
  cp -a ./efpic-pro "$PLUGINPATH/"
fi

echo "Deploy pabeigts → $PLUGINPATH/efpic un efpic-pro"
