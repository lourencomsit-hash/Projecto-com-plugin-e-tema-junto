#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────
# build.sh — Cria ZIP do plugin Breeze SEO para upload no WP
# Uso: bash build.sh
# ──────────────────────────────────────────────────────────────

set -e

PLUGIN_SLUG="plugin-seo-breeze"
VERSION=$(grep "Version:" breeze-seo.php | head -1 | awk '{ print $NF }')
OUTPUT="../${PLUGIN_SLUG}-${VERSION}.zip"

echo "Building ${PLUGIN_SLUG} v${VERSION}..."

# Remove old build if exists
[ -f "$OUTPUT" ] && rm "$OUTPUT"

# Create zip (exclude dev/meta files)
zip -r "$OUTPUT" . \
  --exclude "*.git*" \
  --exclude "build.sh" \
  --exclude "*.bak" \
  --exclude "*.md" \
  --exclude "*.DS_Store" \
  --exclude "__MACOSX" \
  --exclude "node_modules/*" \
  --exclude ".github/*"

echo "Done: $OUTPUT"
ls -lh "$OUTPUT"
