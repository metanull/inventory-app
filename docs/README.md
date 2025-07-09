# GitHub Pages & Jekyll Setup

This directory contains the Jekyll configuration for automatically generating GitHub Pages from commit history.

## How It Works

### Automated Blog Generation

1. **Trigger**: Every push to `main` branch or merged pull request
2. **Commit Analysis**: GitHub Actions scans all commits on main branch
3. **Post Generation**: Creates Jekyll blog post for each commit with:
   - Commit details (author, date, hash, statistics)
   - Full commit message
   - Files changed
   - Diff summary
4. **Site Build**: Jekyll builds static site with all posts
5. **Deployment**: Automatically deploys to GitHub Pages

### File Structure

```
docs/
├── _config.yml           # Jekyll configuration
├── Gemfile              # Ruby dependencies
├── index.md             # Home page content
├── archive.md           # Archive page for all commits
├── _layouts/            # Custom Jekyll layouts
│   ├── home.html        # Home page layout
│   └── post.html        # Blog post layout
├── _posts/              # Auto-generated blog posts
│   └── YYYY-MM-DD-title.md
└── README.md            # This file
```

## Features

### 🤖 Fully Automated

- **No Local Installation Required**: Everything runs in GitHub Actions
- **Zero Maintenance**: Posts generated automatically from commits
- **CI/CD Integration**: Builds and deploys after each merge

### 📝 Rich Content

- **Commit Details**: Author, date, hash, file statistics
- **Code Diffs**: Syntax-highlighted code changes
- **File Tracking**: Complete list of modified files
- **Search & Navigation**: Post navigation and archive

### 🎨 Professional Styling

- **Minima Theme**: Clean, responsive design
- **Syntax Highlighting**: Rouge syntax highlighter
- **GitHub Integration**: Direct links to commits and repository
- **SEO Optimized**: Proper meta tags and structured data

## Configuration

### GitHub Actions Workflow

Located at `.github/workflows/github-pages.yml`:

- **Triggers**: Push to main, merged PRs
- **Permissions**: Pages write, contents read
- **Jobs**: Generate posts → Build Jekyll → Deploy

### Jekyll Configuration

Key settings in `_config.yml`:

- **Theme**: Minima (GitHub Pages compatible)
- **Plugins**: Feed, sitemap, SEO tag
- **Pagination**: 10 posts per page
- **Permalinks**: `/commits/YYYY/MM/DD/title/`

## GitHub Pages Settings

### Repository Settings

1. Go to **Settings** → **Pages**
2. **Source**: GitHub Actions
3. **Custom Domain** (optional): Configure your domain
4. **HTTPS**: Automatically enabled

### Required Permissions

The workflow needs these permissions:

```yaml
permissions:
  contents: read # Read repository content
  pages: write # Deploy to GitHub Pages
  id-token: write # OIDC token for deployment
```

## Local Development (Optional)

While not required, you can test locally:

### Prerequisites

```bash
# Install Ruby and Jekyll (if desired)
gem install bundler jekyll
```

### Commands

```bash
# Install dependencies
cd docs && bundle install

# Serve locally (with live reload)
composer docs-serve

# Build site
composer docs-build
```

### Note

Local development is **completely optional** since everything runs automatically in GitHub Actions.

## Customization

### Post Template

Posts are generated with this template:

```markdown
---
layout: post
title: "Commit Subject"
date: YYYY-MM-DD HH:MM:SS +0000
author: Author Name
commit: commit_hash
categories: [commits, development]
tags: [git, changelog]
---

## Commit Details

[Automatic generation of commit info]

## Commit Message

[Full commit message]

## Files Modified

[List of changed files]

## Changes Summary

[Git diff statistics]
```

### Styling

Customize appearance by modifying:

- `_layouts/post.html` - Individual post layout
- `_layouts/home.html` - Home page layout
- CSS styles embedded in layouts

### Navigation

- **Home**: Latest 10 commits
- **Archive**: All commits organized by year
- **RSS Feed**: Available at `/feed.xml`
- **Post Navigation**: Previous/next commit links

## Benefits

### For Developers

- **Automatic Documentation**: Every commit becomes a blog post
- **Project Transparency**: Public development history
- **Zero Overhead**: No manual blog maintenance required

### For Project Management

- **Progress Tracking**: Visual timeline of development
- **Change Documentation**: Detailed record of all modifications
- **Public Engagement**: Stakeholders can follow progress

### for CI/CD

- **Integrated Workflow**: Blog updates with code changes
- **Performance**: Fast Jekyll builds (~30 seconds)
- **Reliability**: GitHub Actions handles all automation

## Troubleshooting

### Common Issues

1. **Posts Not Generating**
   - Check GitHub Actions logs
   - Verify workflow permissions
   - Ensure commits exist on main branch

2. **Build Failures**
   - Check Jekyll build logs in Actions
   - Verify `_config.yml` syntax
   - Check for Gemfile issues

3. **Pages Not Deploying**
   - Verify Pages settings in repository
   - Check deployment permissions
   - Review Actions deployment logs

### Debug Commands

```bash
# Check workflow status
gh run list --workflow=github-pages.yml

# View specific run logs
gh run view [run-id] --log

# Validate Jekyll config locally
cd docs && bundle exec jekyll doctor
```

## Security

### Permissions

- **Minimal Access**: Only required permissions granted
- **OIDC Authentication**: Secure deployment without secrets
- **Read-Only Content**: No write access to main repository

### Content Safety

- **Automated Sanitization**: Safe filename generation
- **HTML Escaping**: Proper escaping of commit messages
- **Link Validation**: Direct GitHub integration for commit links

---

This setup provides a complete solution for automated project documentation through commit-based blog generation, requiring zero maintenance while providing rich, searchable project history.
