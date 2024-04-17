#!/bin/bash
# This script is used to serve the Jekyll site locally with automatic rebuilding.
# 'bundle exec' ensures we're using the correct versions of each gem according to our Gemfile.lock.
# 'jekyll serve' starts a Jekyll development server.
# '--watch' option automatically rebuilds the site when files are modified.
bundle exec jekyll serve --watch
