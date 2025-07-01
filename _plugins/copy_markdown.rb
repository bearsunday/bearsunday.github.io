Jekyll::Hooks.register :site, :post_write do |site|
  # Copy markdown files to _site for direct access
  source_dir = File.join(site.source, 'manuals')
  dest_dir = File.join(site.dest, 'manuals')
  
  if Dir.exist?(source_dir)
    # Create destination directory if it doesn't exist
    FileUtils.mkdir_p(dest_dir) unless Dir.exist?(dest_dir)
    
    # Find all .md files and copy them
    Dir.glob(File.join(source_dir, '**', '*.md')).each do |md_file|
      # Calculate relative path
      relative_path = Pathname.new(md_file).relative_path_from(Pathname.new(source_dir))
      dest_file = File.join(dest_dir, relative_path)
      
      # Create destination directory if needed
      FileUtils.mkdir_p(File.dirname(dest_file))
      
      # Copy the file
      FileUtils.cp(md_file, dest_file)
      puts "Copied #{relative_path} for llms.txt compatibility"
    end
  end
end