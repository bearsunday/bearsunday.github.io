#!/bin/bash
set -euo pipefail

# This script is used to serve the Jekyll site locally with automatic rebuilding.
# 'bundle exec' ensures we're using the correct versions of each gem according to our Gemfile.lock.
# 'jekyll serve' starts a Jekyll development server.
# '--watch' option automatically rebuilds the site when files are modified.
#
# `llms-full.txt` is regenerated from `llms.txt` here and in CI; it is a
# gitignored build artifact.

echo "Starting Jekyll server (watch mode)..."
php bin/gen_llms.php

# Build the site once, then generate the Pagefind search index so the
# in-page search works locally. Jekyll keeps pre-existing static files in
# _site during watch rebuilds, so the index survives subsequent edits.
# Skip silently if npx/pagefind is unavailable (search will just be absent).
bundle exec jekyll build
if command -v npx >/dev/null 2>&1; then
  npx --yes pagefind@latest --site _site || echo "Warning: pagefind index generation failed; search disabled."
else
  echo "Note: npx not found; skipping Pagefind search index."
fi

bundle exec jekyll serve --watch --port 4001
