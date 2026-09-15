#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
SLUG="akash-visual-layout-builder"
STAGE="$(mktemp -d)"
cleanup() { rm -rf "$STAGE"; }
trap cleanup EXIT

echo "Building editor assets..."
(cd "$ROOT/editor" && npm run build)
cp -f "$ROOT/editor/public/favicon.svg" "$ROOT/editor/public/icons.svg" "$ROOT/assets/build/" 2>/dev/null || true

# Ensure directory silencers exist in the build output folder.
mkdir -p "$ROOT/assets/build"
if [[ ! -f "$ROOT/assets/build/index.php" ]]; then
  printf '%s\n' '<?php' '// Silence is golden.' > "$ROOT/assets/build/index.php"
fi

echo "Creating Akash Visual Layout Builder zip..."
mkdir -p "$STAGE/$SLUG"
rsync -a \
  --exclude 'editor/node_modules/' \
  --exclude 'editor/dist/' \
  --exclude 'local-config.php' \
  --exclude 'local-config.example.php' \
  --exclude '.git/' \
  --exclude '.gitignore' \
  --exclude '.gitattributes' \
  --exclude '.nvmrc' \
  --exclude '.cursor/' \
  --exclude '.tmp-gh/' \
  --exclude '*.zip' \
  --exclude '.DS_Store' \
  --exclude 'WORDPRESS-ORG.md' \
  --exclude 'package.sh' \
  "$ROOT/" "$STAGE/$SLUG/"

rm -f "$ROOT/${SLUG}.zip"
(
  cd "$STAGE"
  # -9 maximizes deflate; JPEGs stay near original size but other files shrink.
  zip -9 -r "$ROOT/${SLUG}.zip" "$SLUG" \
    -x "*.DS_Store" \
    -x "*/.DS_Store"
)

BYTES=$(wc -c < "$ROOT/${SLUG}.zip" | tr -d ' ')
MB=$(awk -v b="$BYTES" 'BEGIN { printf "%.2f", b / 1024 / 1024 }')
echo "Zip size: ${MB} MB (${BYTES} bytes)"
if (( BYTES > 10485760 )); then
  echo "WARNING: zip exceeds 10 MB WordPress.org soft limit." >&2
fi

echo "Done:"
echo "  $ROOT/${SLUG}.zip"
