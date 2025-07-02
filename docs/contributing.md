---
layout: default
title: Contributing
nav_order: 2
---

# Contributing Guidelines

We welcome contributions to the Inventory Management UI! This document provides guidelines for contributing to the project.

## 🚀 Getting Started

### 1. Fork and Clone

```bash
# Fork the repository on GitHub, then clone your fork
git clone https://github.com/YOUR-USERNAME/inventory-management-ui.git
cd inventory-management-ui

# Add the original repository as upstream
git remote add upstream https://github.com/ORIGINAL-OWNER/inventory-management-ui.git
```

### 2. Set Up Development Environment

```bash
# Install dependencies
npm install

# Set up environment variables
cp .env.example .env
# Edit .env with your configuration

# Start development server
npm run dev
```

## 📋 Essential Guidelines

Before contributing, please review our development guidelines:

### [📖 Coding Guidelines](guidelines/coding-guidelines/)
- Vue.js & TypeScript standards
- Component structure and organization
- Styling with Tailwind CSS
- Quality controls and best practices
- Architecture patterns

### [🧪 Testing Guidelines](guidelines/testing/)
- Testing requirements and coverage
- Unit and component testing standards
- Code review criteria

### [🔗 API Integration Guidelines](guidelines/api-integration/)
- Backend API integration patterns
- Authentication and error handling

## 📝 Contribution Process

### 1. Create a Feature Branch

```bash
# Create and switch to a new branch
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/bug-description
```

### 2. Make Your Changes

- Write clear, concise code following our [coding standards](guidelines/coding-guidelines/)
- Add tests for new functionality (see [testing guidelines](guidelines/testing/))
- Update documentation as needed
- Follow the existing project structure

### 3. Commit Your Changes

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```bash
# Examples of good commit messages
git commit -m "feat: add tag management to item detail view"
git commit -m "fix: resolve routing issue with detail views"
git commit -m "docs: update API integration documentation"
git commit -m "test: add unit tests for auth store"
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
# Install GitHub CLI
# Windows: choco install gh
# macOS: brew install gh
# Linux: sudo apt install gh

# Authenticate (one-time setup)
gh auth login

# Create PR with auto-merge and squash
gh pr create --title "feat: add new feature" --body "Description of changes" \
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
- [ ] No linting errors
- [ ] TypeScript compilation successful
- [ ] Build completes without errors

### Testing
- [ ] Tests written for new functionality (see [testing guidelines](guidelines/testing/))
- [ ] All tests pass
- [ ] Test coverage meets requirements (80%+ for new code)

### Documentation
- [ ] Code is self-documenting
- [ ] Complex logic is commented
- [ ] Documentation updated if needed

### Git
- [ ] Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/)
- [ ] Branch is up to date with main
- [ ] No merge conflicts

## 🔍 Code Review Process

### What We Look For

1. **Code Quality**
   - Follows our [coding guidelines](guidelines/coding-guidelines/)
   - Proper error handling
   - Clean, readable code structure

2. **Testing**
   - Meets our [testing requirements](guidelines/testing/)
   - Edge cases covered

3. **Performance**
   - No unnecessary re-renders
   - Efficient API calls
   - Proper loading states

4. **Accessibility**
   - Semantic HTML
   - Proper ARIA labels
   - Keyboard navigation support

## 🎯 Areas for Contribution

We especially welcome contributions in these areas:

- **New features** - enhance existing functionality
- **Bug fixes** - help us improve stability
- **Performance improvements** - optimize the application
- **Accessibility** - make the app more inclusive
- **Documentation** - improve guides and examples
- **Testing** - increase test coverage
- **UI/UX improvements** - enhance user experience
- **Entity enhancements** - improve CRUD operations for all entities
- **File upload improvements** - enhance image upload functionality

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

Thank you for contributing to the Inventory Management UI! 🎉

---

*Last updated: {{ site.time | date: "%B %d, %Y" }}*
