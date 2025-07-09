#!/usr/bin/env python3
"""
Client Documentation Generator for Jekyll
Generates index files for TypeScript API client documentation
Author: Generated for Inventory Management UI
Usage: python3 generate-client-docs.py
"""

import os
import sys
import re
from datetime import datetime
from pathlib import Path
import logging

# Configuration
CLIENT_DOCS_DIR = "api-client/docs"
JEKYLL_CLIENT_DIR = "docs/api-client"
LOG_FILE = "docs/client-docs.log"

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE),
        logging.StreamHandler(sys.stdout)
    ]
)

logger = logging.getLogger(__name__)

class ClientDocGenerator:
    def __init__(self):
        self.client_docs_dir = Path(CLIENT_DOCS_DIR)
        self.jekyll_client_dir = Path(JEKYLL_CLIENT_DIR)
        self.repo_root = Path(".")
        
    def sanitize_title(self, text):
        """Sanitize text for use in titles"""
        sanitized = re.sub(r'[^a-zA-Z0-9\s-]', '', text)
        sanitized = re.sub(r'\s+', ' ', sanitized)
        sanitized = sanitized.strip()
        return sanitized
    
    def escape_yaml_string(self, text):
        """Escape a string for safe use in YAML front matter"""
        if not text:
            return '""'
        
        # If the string contains quotes, backslashes, or other special YAML characters,
        # we need to escape them or use literal block style
        if '"' in text or '\\' in text or '\n' in text or '\r' in text:
            # Escape quotes and backslashes
            escaped = text.replace('\\', '\\\\').replace('"', '\\"')
            return f'"{escaped}"'
        else:
            # Safe to use as-is with quotes
            return f'"{text}"'
    
    def jekyll_url_sanitize(self, filename_stem):
        """Convert filename stem to Jekyll-style URL format (lowercase with hyphens)"""
        # Jekyll converts filenames to lowercase and replaces underscores with hyphens
        url_safe = filename_stem.lower().replace('_', '-')
        return url_safe
    
    def sanitize_filename(self, text):
        """Sanitize text for use in filenames"""
        sanitized = re.sub(r'[^a-zA-Z0-9._-]', '_', text)
        sanitized = re.sub(r'_+', '_', sanitized)
        if len(sanitized) > 127:
            sanitized = sanitized[:127]
        return sanitized.strip('_')
    
    def extract_title_from_md(self, filepath):
        """Extract title from markdown file"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                
                # Look for YAML front matter title first
                yaml_match = re.search(r'^---\s*\n.*?^title:\s*(.+)$.*?^---\s*\n', content, re.MULTILINE | re.DOTALL)
                if yaml_match:
                    title = yaml_match.group(1).strip().strip('"')
                    return title
                
                # Look for first H1 header
                h1_match = re.search(r'^# (.+)$', content, re.MULTILINE)
                if h1_match:
                    return h1_match.group(1).strip()
                
                # Fall back to filename without extension
                return filepath.stem
        except Exception as e:
            logger.warning(f"Could not extract title from {filepath}: {e}")
            return filepath.stem
    
    def categorize_files(self, md_files):
        """Categorize markdown files by type"""
        categories = {
            'APIs': [],
            'Models': [],
            'Requests': [],
            'Responses': [],
            'Other': []
        }
        
        for md_file in md_files:
            filename = md_file.name
            title = self.extract_title_from_md(md_file)
            
            # Categorize based on filename patterns
            if filename.endswith('Api.md'):
                categories['APIs'].append({'file': md_file, 'title': title})
            elif any(keyword in filename for keyword in ['Request.md', 'StoreRequest.md', 'UpdateRequest.md']):
                categories['Requests'].append({'file': md_file, 'title': title})
            elif any(keyword in filename for keyword in ['Response.md', '200Response.md', '201Response.md', '404Response.md', '422Response.md', '500Response.md']):
                categories['Responses'].append({'file': md_file, 'title': title})
            elif filename.endswith('Resource.md'):
                categories['Models'].append({'file': md_file, 'title': title})
            else:
                categories['Other'].append({'file': md_file, 'title': title})
        
        # Sort each category by title
        for category in categories.values():
            category.sort(key=lambda x: x['title'])
        
        return categories
    
    def generate_jekyll_pages(self, categories):
        """Generate Jekyll pages for each markdown file"""
        logger.info("Generating Jekyll pages for client documentation...")
        
        # Create Jekyll client directory
        self.jekyll_client_dir.mkdir(parents=True, exist_ok=True)
        
        # Track generated files
        generated_files = []
        
        for category_name, files in categories.items():
            if not files:
                continue
                
            for file_info in files:
                md_file = file_info['file']
                title = file_info['title']
                
                # Read original content
                try:
                    with open(md_file, 'r', encoding='utf-8') as f:
                        original_content = f.read()
                except Exception as e:
                    logger.warning(f"Could not read {md_file}: {e}")
                    continue
                
                # Fix broken links in the content
                fixed_content = self.fix_broken_links(original_content)
                
                # Generate Jekyll filename
                jekyll_filename = f"{md_file.stem.lower().replace('_', '-')}.md"
                jekyll_filepath = self.jekyll_client_dir / jekyll_filename
                
                # Create Jekyll page with front matter
                escaped_title = self.escape_yaml_string(title)
                escaped_category = self.escape_yaml_string(category_name)
                
                jekyll_content = f"""---
layout: default
title: {escaped_title}
parent: TypeScript API Client
nav_order: 1
category: {escaped_category}
---

{fixed_content}

---

*This documentation was automatically generated from the TypeScript API client.*
"""
                
                # Write Jekyll page
                with open(jekyll_filepath, 'w', encoding='utf-8') as f:
                    f.write(jekyll_content)
                
                generated_files.append(jekyll_filename)
                logger.info(f"Generated Jekyll page: {jekyll_filename}")
        
        return generated_files
    
    def generate_main_index(self, categories):
        """Generate main index file for client documentation"""
        logger.info("Generating main client documentation index...")
        
        index_content = f"""---
layout: default
title: TypeScript API Client
nav_order: 4
has_children: true
---

# TypeScript API Client Documentation

This section contains the automatically generated documentation for the TypeScript-Axios API client.

## Installation

```bash
npm install @metanull/inventory-app-api-client@latest
```

## Usage

```typescript
import {{ Configuration, DefaultApi }} from '@metanull/inventory-app-api-client';

const api = new DefaultApi(new Configuration({{ basePath: 'https://your.api.url' }}));
api.addressIndex().then(response => console.log(response.data));
```

## Documentation Categories

"""
        
        # Add categories with file counts
        for category_name, files in categories.items():
            if not files:
                continue
                
            count = len(files)
            index_content += f"### {category_name} ({count} items)\n\n"
            
            # Add all items (not just first 5)
            for file_info in files:
                title = file_info['title']
                jekyll_filename = file_info['file'].stem.lower().replace('_', '-')
                index_content += f"- [{title}]({{{{ site.baseurl }}}}/api-client/{jekyll_filename}/)\n"
            
            index_content += "\n"
        
        index_content += f"""## Package Information

- **Package Name:** `@metanull/inventory-app-api-client`
- **Repository:** [GitHub Packages](https://github.com/metanull/inventory-app/packages)
- **Generated:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

## Generation

The client is generated using:
```powershell
. ./scripts/generate-api-client.ps1
```

And published using:
```powershell
. ./scripts/publish-api-client.ps1 -Credential (Get-Credential -Message "GitHub PAT")
```

---

*This documentation was automatically generated on {datetime.now().isoformat()}*
"""
        
        # Write main index
        index_filepath = self.jekyll_client_dir / "index.md"
        with open(index_filepath, 'w', encoding='utf-8') as f:
            f.write(index_content)
        
        logger.info("Generated main client documentation index")
    
    def validate_directories(self):
        """Validate that required directories exist"""
        if not self.client_docs_dir.exists():
            logger.error(f"Client documentation directory not found: {self.client_docs_dir}")
            logger.error("Please generate the API client first using: . ./scripts/generate-api-client.ps1")
            sys.exit(1)
        
        # Check if there are any markdown files
        md_files = list(self.client_docs_dir.glob("*.md"))
        if not md_files:
            logger.error(f"No markdown files found in: {self.client_docs_dir}")
            logger.error("Please generate the API client first using: . ./scripts/generate-api-client.ps1")
            sys.exit(1)
        
        logger.info(f"Found {len(md_files)} markdown files to process")
    
    def fix_broken_links(self, content):
        """Fix broken links in the generated documentation"""
        # Replace broken navigation links - fix the actual format with double brackets
        content = re.sub(r'\[\[Back to top\]\]\(#\)', '[Back to top](#)', content)
        content = re.sub(r'\[\[Back to API list\]\]\(\.\.\/README\.md#documentation-for-api-endpoints\)', '[Back to API list]({{ site.baseurl }}/api-client/)', content)
        content = re.sub(r'\[\[Back to Model list\]\]\(\.\.\/README\.md#documentation-for-models\)', '[Back to Model list]({{ site.baseurl }}/api-client/)', content)
        content = re.sub(r'\[\[Back to README\]\]\(\.\.\/README\.md\)', '[Back to README]({{ site.baseurl }}/api-client/)', content)
        
        return content
    
    def run(self):
        """Main execution function"""
        logger.info("Starting client documentation generator...")
        
        # Validate directories
        self.validate_directories()
        
        # Get all markdown files
        md_files = list(self.client_docs_dir.glob("*.md"))
        logger.info(f"Processing {len(md_files)} markdown files...")
        
        # Categorize files
        categories = self.categorize_files(md_files)
        
        # Log category summary
        for category_name, files in categories.items():
            if files:
                logger.info(f"{category_name}: {len(files)} files")
        
        # Generate Jekyll pages
        generated_files = self.generate_jekyll_pages(categories)
        
        # Generate main index
        self.generate_main_index(categories)
        
        logger.info("Client documentation generation completed!")
        logger.info(f"Generated {len(generated_files)} Jekyll pages in: {self.jekyll_client_dir}")
        logger.info("Files are ready for Jekyll to process")

if __name__ == "__main__":
    generator = ClientDocGenerator()
    generator.run()
