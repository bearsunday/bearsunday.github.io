#!/bin/bash
set -euo pipefail

# Copy markdown files to _site directory for llms.txt standard compliance
# This script should be run after Jekyll build
# It removes Jekyll front matter from the copied files

SOURCE_DIR="manuals"
DEST_DIR="_site/manuals"

echo "Copying markdown files for llms.txt compliance..."

# Check if source directory exists
if [[ ! -d "$SOURCE_DIR" ]]; then
    echo "Error: Source directory '$SOURCE_DIR' does not exist"
    exit 1
fi

# Create destination directory
mkdir -p "$DEST_DIR"

# Find all .md files in manuals directory and copy them to _site
find "$SOURCE_DIR" -name "*.md" -type f -print0 | while IFS= read -r -d '' file; do
    # Get relative path
    relative_path="${file#$SOURCE_DIR/}"
    dest_file="$DEST_DIR/$relative_path"

    # Create destination directory if it doesn't exist
    mkdir -p "$(dirname "$dest_file")"

    # Copy the file and remove front matter using awk (more reliable)
    awk '
        BEGIN { in_frontmatter = 0; first_line = 1 }
        /^---$/ && first_line { in_frontmatter = 1; first_line = 0; next }
        /^---$/ && in_frontmatter { in_frontmatter = 0; next }
        !in_frontmatter { print }
        { first_line = 0 }
    ' "$file" > "$dest_file"

    echo "Copied and cleaned: $relative_path"
done

echo "Markdown files copied successfully!"
