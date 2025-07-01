#!/bin/bash
# This script is used to serve the Jekyll site locally with automatic rebuilding.
# 'bundle exec' ensures we're using the correct versions of each gem according to our Gemfile.lock.
# 'jekyll serve' starts a Jekyll development server.
# '--watch' option automatically rebuilds the site when files are modified.

# Copy markdown files for llms.txt compliance after initial build
echo "Starting Jekyll server with llms.txt compliance..."
bundle exec jekyll build
./bin/copy_markdown_files.sh
bundle exec jekyll serve --watch
