require 'fileutils'
require 'yaml'
require 'pathname'

def convert_to_markdown_filename(base_name)
  # Generic kebab-case to CamelCase converter
  # This handles both underscore and hyphen separators
  base_name.split(/[_-]/).map(&:capitalize).join + '.md'
end

def extract_order_from_contents(language)
  # Read contents.html to get the proper order
  contents_file = File.expand_path("../_includes/manuals/1.0/#{language}/contents.html", __dir__)
  unless File.exist?(contents_file)
    puts "Warning: Contents file not found: #{contents_file}"
    return nil
  end

  contents = File.read(contents_file)

  # Extract permalinks from nav items
  permalinks = contents.scan(/href="\/manuals\/1\.0\/#{language}\/([^"]+\.html)"/).flatten

  # Validate that we found some permalinks
  if permalinks.empty?
    puts "Warning: No permalinks found in #{contents_file}. Navigation structure may have changed."
    return nil
  end

  puts "Found #{permalinks.length} pages in navigation order"

  # Convert HTML filenames to markdown filenames
  markdown_files = permalinks.map do |permalink|
    # Remove .html extension
    base = permalink.sub('.html', '')

    # Skip AI assistant and other non-documentation pages
    skip_pages = ['ai-assistant', 'index', '1page']
    next nil if skip_pages.include?(base)

    # Convert kebab-case to CamelCase
    convert_to_markdown_filename(base)
  end.compact

  markdown_files
end

def strip_frontmatter(content)
  # Remove Jekyll frontmatter only from the very beginning of the file
  # Support both LF (\n) and CRLF (\r\n) line endings
  content.sub(/\A---\s*\r?\n.*?\r?\n---\s*\r?\n/m, '')
end

def generate_combined_file(language, intro_message)
  source = Pathname.new(__dir__).join("..", "manuals/1.0/#{language}")
  output_file = source.join("1page.md")

  puts "Processing #{language} documentation..."
  raise "Source folder does not exist!" unless source.directory?

  # Determine file order from contents.html or fallback to alphabetical
  file_order = extract_order_from_contents(language)
  if file_order.nil? || file_order.empty?
    puts "Warning: Could not extract order from contents.html, using alphabetical order"
    main_md_files = source.glob("*.md")
                          .map(&:basename)
                          .map(&:to_s)
                          .reject { |f| %w[1page.md ai-assistant.md].include?(f) }
                          .sort
    bp_md_files = source.join("bp").directory? ?
                  source.join("bp").glob("*.md").map(&:basename).map(&:to_s).sort : []
    file_order = main_md_files + bp_md_files.map { |f| "bp/#{f}" }
  end

  # Gather all files: main files in order + best practices
  all_files = file_order.map { |fn| source.join(fn) }.select(&:file?)
  main_files = all_files.reject { |f| f.dirname.basename.to_s == "bp" }
  bp_files = all_files.select { |f| f.dirname.basename.to_s == "bp" }

  files_processed = 0

  File.open(output_file, "w") do |out|
    # Write header
    out.write <<~HEADER
      ---
      layout: docs-#{language}
      title: BEAR.Sunday Complete Manual
      category: Manual
      permalink: /manuals/1.0/#{language}/1page.html
      ---

      # BEAR.Sunday Complete Manual

      #{intro_message}

      ***
    HEADER

    # Add best practices header before BP files
    bp_start_index = main_files.length

    # Process all files in a single pass
    all_files.each_with_index do |path, idx|
      begin
        content = strip_frontmatter(path.read).strip
        next if content.empty?

        # Add separator between sections (except first)
        out.write("\n***\n\n") if idx > 0

        # Add BP section header if this is the first BP file
        if idx == bp_start_index && idx < all_files.length
          out.write("## Best Practices Details\n\n")
        end

        # Handle best practices heading conversion
        if path.dirname.basename.to_s == "bp"
          if content =~ /\A\#{1,6}\s+(.+?)\n(.*)/m
            heading_text = $1
            remaining_content = $2
            out.write("\n### #{heading_text}\n\n#{remaining_content}\n")
          else
            # No heading found, generate from filename
            title = path.basename(".md").to_s.gsub(/([A-Z])/, ' \1').strip
            out.write("\n### #{title}\n\n#{content}\n")
          end
          puts "  Added BP: #{path.basename}"
        else
          out.write(content + "\n")
          puts "  Added: #{path.basename}"
        end

        files_processed += 1
      rescue => e
        puts "  Error processing #{path.basename}: #{e.message}"
      end
    end
  end

  puts "Generated: #{output_file}"
  puts "Total sections: #{files_processed}"
end

# Generate combined files for both languages
generate_combined_file("en", "This comprehensive manual contains all BEAR.Sunday documentation in a single page for easy reference, printing, or offline viewing.")
generate_combined_file("ja", "このページは、BEAR.Sundayの全ドキュメントを1ページにまとめた包括的なマニュアルです。参照、印刷、オフライン閲覧に便利です。")
