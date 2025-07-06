---
layout: default
title: Contributing
nav_order: 3
---

# Contributing Guidelines

We welcome contributions to the Inventory Management API! This document provides guidelines for contributing to this RESTful API project built with PHP 8.2+ and Laravel 12.

## 🚀 Getting Started

### 1. Fork and Clone

```bash
# Fork the repository on GitHub, then clone your fork
git clone https://github.com/YOUR-USERNAME/inventory-app.git
cd inventory-app

# Add the original repository as upstream
git remote add upstream https://github.com/metanull/inventory-app.git
```

### 2. Retrieve Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (for frontend assets and code quality tools)
npm install
```

### 3. Set Up Development Environment

#### Using our composer scripts

_See `composer.json` for a list of ci/cd scripts_

```bash
composer ci-reset
```

#### Or using artisan

```bash
# Set up environment variables
cp .env.example .env
# Edit .env with your configuration

# Generate application key
php artisan key:generate

# Set up database and run migrations
php artisan migrate --seed

# Build frontend assets
npm run build
```

### 4. Start the Development Server

```bash
php artisan serve
```

## 📋 Essential Guidelines

Before contributing, please review our development guidelines:

### [📖 Coding Guidelines](guidelines/coding-guidelines/)

- PHP 8.2+ and Laravel 12 standards
- Model, Controller, and Resource structure
- Database design with Eloquent ORM
- Quality controls and best practices
- API architecture patterns
- RESTful API design principles

### [🧪 Testing Guidelines](guidelines/testing/)

- Testing requirements and coverage (924+ tests)
- Unit, Feature, and Integration testing standards
- PHPUnit and Laravel testing best practices
- Code review criteria and validation

## 📝 Contribution Process

### 1. Create a Feature Branch

```bash
# Create and switch to a new branch
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/bug-description
```

### 2. Make Your Changes

- Write clear, concise PHP code following our [coding standards](guidelines/coding-guidelines/)
- Follow Laravel best practices and conventions
- Add tests for new functionality (see [testing guidelines](guidelines/testing/))
- Update API documentation as needed (uses Scramble for auto-generation)
- Follow the existing project structure and patterns

### 3. Commit Your Changes

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```bash
# Examples of good commit messages
git commit -m "feat: add new API endpoint for tag management"
git commit -m "fix: resolve validation issue in item controller"
git commit -m "docs: update API documentation for new endpoints"
git commit -m "test: add unit tests for markdown service"
```

### 4. Push and Create Pull Request

```bash
# Push your branch
git push origin feature/your-feature-name
```

#### Option A: Using GitHub Web Interface

1. Navigate to your fork on GitHub
2. Click "Compare & pull request"
3. Provide a clear title and description
4. Reference any related issues
5. Wait for code review

#### Option B: Using GitHub CLI (Recommended)

```bash
# Install GitHub CLI (if not already installed)
# Windows PowerShell: winget install GitHub.cli
# Or use chocolatey: choco install gh

# Authenticate (one-time setup)
gh auth login

# Create PR with auto-merge and squash
gh pr create --title "feat: add new API endpoint" --body "Description of changes" \
  --assignee @me \
  --label "enhancement" \
  --auto-merge \
  --squash
```

## ✅ Pre-Submission Checklist

Before submitting your PR, ensure you've followed our guidelines:

### Code Quality

- [ ] Code follows our [coding standards](guidelines/coding-guidelines/)
- [ ] All [quality controls](guidelines/coding-guidelines/#quality-controls) pass
- [ ] No linting errors (`composer ci-lint`)
- [ ] PHP syntax is valid
- [ ] Build completes without errors (`composer ci-build`)

### Testing

- [ ] Tests written for new functionality (see [testing guidelines](guidelines/testing/))
- [ ] All tests pass (`composer ci-test`)
- [ ] Test coverage maintained (aim for 80%+ on new code)
- [ ] Database migrations tested

### Documentation

- [ ] Code is self-documenting with proper PHPDoc comments
- [ ] Complex logic is commented
- [ ] API documentation updated if needed (Scramble auto-generates from code)

### Git

- [ ] Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/)
- [ ] Branch is up to date with main
- [ ] No merge conflicts

## 🔍 Code Review Process

### What We Look For

1. **Code Quality**
    - Follows our [coding guidelines](guidelines/coding-guidelines/)
    - Proper error handling and validation
    - Clean, readable PHP code structure
    - Laravel best practices compliance

2. **Testing**
    - Meets our [testing requirements](guidelines/testing/)
    - Unit tests for models and services
    - Feature tests for API endpoints
    - Edge cases covered

3. **Performance**
    - Efficient database queries (avoid N+1 problems)
    - Proper use of Eloquent relationships
    - Appropriate caching where needed
    - Optimized API responses

4. **Security**
    - Input validation and sanitization
    - Proper authentication and authorization
    - No SQL injection vulnerabilities
    - Secure file upload handling

## 🎯 Areas for Contribution

We especially welcome contributions in these areas:

- **New API endpoints** - enhance existing functionality
- **Bug fixes** - help us improve stability
- **Performance improvements** - optimize database queries and API responses
- **Security enhancements** - strengthen authentication and validation
- **Documentation** - improve guides and API documentation
- **Testing** - increase test coverage and add edge case tests
- **Entity enhancements** - improve CRUD operations for all models
- **Image processing improvements** - enhance upload and processing functionality
- **Database optimizations** - improve migrations and seeding
- **API feature additions** - new endpoints for better functionality

## 📞 Getting Help

- **GitHub Issues** - for bugs and feature requests
- **GitHub Discussions** - for questions and general discussion
- **Code Review** - maintainers will provide feedback on PRs

For technical questions about our development practices, please refer to our [guidelines section](guidelines/).

## 🏆 Recognition

Contributors will be recognized in:

- The project's README
- Release notes for significant contributions
- GitHub's contributor list

Thank you for contributing to the Inventory Management API! 🎉

---

_Last updated: {{ site.time | date: "%B %d, %Y" }}_
