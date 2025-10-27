Jekyll::Hooks.register :site, :after_init do |site|
  # Define a helper method to transform README.md links to Jekyll permalinks
  def transform_readme_links(content)
    # Transform links to /scripts/README.md (with or without anchors)
    # Note: Omit trailing slash as per Jekyll conventions for file links
    content = content.gsub(/\[([^\]]+)\]\(([\.\/]*)scripts\/README\.md(#[^\)]+)?\)/) do
      link_text = $1
      anchor = $3 || ''
      "[#{link_text}](/development/scripts#{anchor})"
    end
    
    # Transform links to /.github/workflows/README.md (with or without anchors)
    content = content.gsub(/\[([^\]]+)\]\(([\.\/]*)\.github\/workflows\/README\.md(#[^\)]+)?\)/) do
      link_text = $1
      anchor = $3 || ''
      "[#{link_text}](/development/workflows#{anchor})"
    end
    
    # Transform links to /docs/README.md (with or without anchors)
    content = content.gsub(/\[([^\]]+)\]\(([\.\/]*)docs\/README\.md(#[^\)]+)?\)/) do
      link_text = $1
      anchor = $3 || ''
      "[#{link_text}](/development/documentation-site#{anchor})"
    end
    
    content
  end
  
  # Copy scripts/README.md to docs/development/scripts.md
  scripts_readme_source = File.join(site.source, '..', 'scripts', 'README.md')
  scripts_readme_dest = File.join(site.source, 'development', 'scripts.md')
  
  if File.exist?(scripts_readme_source)
    content = File.read(scripts_readme_source)
    
    # Transform README.md links to Jekyll permalinks
    content = transform_readme_links(content)
    
    # Add front matter
    front_matter = <<~FRONTMATTER
      ---
      layout: default
      title: Scripts
      parent: Development
      nav_order: 4
      permalink: /development/scripts/
      ---
      
    FRONTMATTER
    
    # Remove the original title (first line starting with #)
    content = content.sub(/^#\s+Scripts\s*\n/, '')
    
    File.write(scripts_readme_dest, front_matter + content)
    puts "Copied scripts/README.md to development/scripts.md"
  end
  
  # Copy .github/workflows/README.md to docs/development/workflows.md
  workflows_readme_source = File.join(site.source, '..', '.github', 'workflows', 'README.md')
  workflows_readme_dest = File.join(site.source, 'development', 'workflows.md')
  
  if File.exist?(workflows_readme_source)
    content = File.read(workflows_readme_source)
    
    # Transform README.md links to Jekyll permalinks
    content = transform_readme_links(content)
    
    # Add front matter
    front_matter = <<~FRONTMATTER
      ---
      layout: default
      title: GitHub Workflows
      parent: Development
      nav_order: 5
      permalink: /development/workflows/
      ---
      
    FRONTMATTER
    
    # Remove the original title (first line starting with #)
    content = content.sub(/^#\s+Workflows\s*\n/, '')
    
    File.write(workflows_readme_dest, front_matter + content)
    puts "Copied .github/workflows/README.md to development/workflows.md"
  end
  
  # Copy docs/README.md to docs/development/documentation-site.md
  docs_readme_source = File.join(site.source, 'README.md')
  docs_readme_dest = File.join(site.source, 'development', 'documentation-site.md')
  
  if File.exist?(docs_readme_source)
    content = File.read(docs_readme_source)
    
    # Transform README.md links to Jekyll permalinks
    content = transform_readme_links(content)
    
    # Add front matter
    front_matter = <<~FRONTMATTER
      ---
      layout: default
      title: Documentation Site
      parent: Development
      nav_order: 6
      permalink: /development/documentation-site/
      ---
      
    FRONTMATTER
    
    # Remove the original title (first line starting with #)
    content = content.sub(/^#\s+Documentation Website\s*\n/, '')
    
    File.write(docs_readme_dest, front_matter + content)
    puts "Copied docs/README.md to development/documentation-site.md"
  end
end
