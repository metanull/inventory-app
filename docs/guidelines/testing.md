---
layout: default
title: Testing
nav_order: 3
parent: Guidelines
has_children: true
---

# Testing Guide

This document provides comprehensive guidance on running tests in the Inventory Management UI application, including both unit tests and integration tests against live APIs.

## 🧪 Testing Requirements

- **Write tests** for all new functionality
- **Update existing tests** when modifying code
- **Minimum 80% test coverage** for new code

### Running Tests

```bash
# Run tests
npm run test

# Run tests with coverage
npm run test:coverage

# Run tests in watch mode
npm run test:watch
```

## 📋 Testing Guidelines

### Unit Tests

- **Test business logic** thoroughly
- **Mock external dependencies** (API calls, etc.)
- **Use descriptive test names**

```typescript
// ✅ Good: Descriptive test structure
describe('AuthStore', () => {
  describe('login', () => {
    it('should set user data when login succeeds', async () => {
      // Test implementation
    })

    it('should handle login errors gracefully', async () => {
      // Test implementation
    })
  })
})
```

### Component Tests

- **Test user interactions**
- **Verify component behavior**
- **Mock router and stores**

```typescript
// ✅ Good: Component test example
describe('ItemDetail.vue', () => {
  it('should display item information correctly', async () => {
    const wrapper = mount(ItemDetail, {
      global: {
        plugins: [router, pinia]
      }
    })
    
    expect(wrapper.text()).toContain('Expected Item Name')
  })
})
```

## 🔍 Code Review Testing Criteria

### What We Look For

1. **Testing Coverage**
   - Adequate test coverage
   - Tests pass consistently
   - Edge cases covered

2. **Test Quality**
   - Tests are maintainable
   - Clear test descriptions
   - Proper mocking strategies

3. **Performance Testing**
   - No unnecessary re-renders
   - Efficient API calls
   - Proper loading states

## Table of Contents

1. [Overview](#overview)
2. [Unit Tests](#unit-tests)
3. [Integration Tests](#integration-tests)
4. [Test Configuration](#test-configuration)
5. [Continuous Integration](#continuous-integration)
6. [Troubleshooting](#troubleshooting)

{: .note }
> **Quick Start**: Run `npm test` for unit tests or `npm run test:integration:guided` for interactive integration testing.

## Overview

The application uses a multi-layered testing strategy:

- **Unit Tests**: Fast, isolated tests using mocks (default test suite)
- **Integration Tests**: Tests against live API endpoints (on-demand only)
- **Component Tests**: UI component behavior and interaction testing
- **E2E Tests**: Full application workflow testing (future enhancement)

### Technology Stack

- **Vitest**: Modern test runner with Vue 3 support
- **Vue Test Utils**: Official Vue.js testing utilities
- **jsdom**: DOM simulation for component testing
- **MSW (Mock Service Worker)**: API mocking for unit tests
- **Axios**: HTTP client testing with interceptors

## Unit Tests

Unit tests are the primary testing method and run automatically in CI/CD. They use mocks to isolate components and API calls.

### Running Unit Tests

```bash
# Run all unit tests once
npm test

# Run tests in watch mode (development)
npm run test:watch

# Run tests with coverage report
npm run test:coverage

# Run tests in UI mode (interactive)
npm run test:ui
```

### Test Structure

```
src/
├── api/__tests__/
│   └── client.test.ts          # API client unit tests
├── components/__tests__/
│   └── *.test.ts              # Component unit tests
├── stores/__tests__/
│   └── *.test.ts              # Pinia store tests
├── views/__tests__/
│   └── *.test.ts              # Page component tests
└── utils/__tests__/
    └── *.test.ts              # Utility function tests
```

### Test Coverage

The project maintains high test coverage requirements:

- **Minimum Coverage**: 80%
- **Statements**: 85%+
- **Branches**: 80%+
- **Functions**: 85%+
- **Lines**: 85%+

### Example Unit Test

```typescript
import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { apiClient } from '@/api/client'
import Countries from '@/views/Countries.vue'

// Mock API client
vi.mock('@/api/client')

describe('Countries.vue', () => {
  test('should load and display countries', async () => {
    // Mock API response
    vi.mocked(apiClient.getCountries).mockResolvedValue([
      { id: '1', name: 'United States', iso_code: 'US' }
    ])

    const wrapper = mount(Countries)
    
    // Wait for async operations
    await wrapper.vm.$nextTick()
    
    // Assert component behavior
    expect(wrapper.text()).toContain('United States')
  })
})
```

## Integration Tests

{: .warning }
> **⚠️ Critical Warning**: Integration tests verify the complete frontend-backend integration using live API endpoints. These tests are **not run automatically** and must be executed manually when needed.

Integration tests verify the complete frontend-backend integration using live API endpoints. These tests are **not run automatically** and must be executed manually when needed.

### ⚠️ Important Warnings

{: .highlight }
- **Use only against test/development databases** - These tests create, modify, and delete real data
- **Never run against production** - Destructive tests will modify your production data  
- **API server must be running** - Tests require a live API endpoint
- **Sequential execution** - Tests run sequentially to avoid data conflicts

### Running Integration Tests

{: .note }
> **Recommended**: Use the guided test runner for first-time setup and safe execution.

```bash
# Interactive guided test runner (recommended)
npm run test:integration:guided

# Read-only tests (safe - only GET requests)
npm run test:integration

# Full CRUD tests (destructive - creates/modifies/deletes data)
npm run test:integration:destructive

# Watch mode for development
npm run test:integration:watch
```

### Integration Test Setup

#### 1. Environment Configuration

Create `.env.integration.local` file:

```bash
# Copy template and customize
cp .env.integration .env.integration.local
```

Configure your test environment:

```bash
# API Configuration - Point to your test API server
VITE_API_BASE_URL=http://127.0.0.1:8000/api

# Test User Credentials (create a test user in your API)
VITE_TEST_EMAIL=test@example.com
VITE_TEST_PASSWORD=password123

# Enable destructive tests (create/update/delete operations)
# WARNING: Only set this to true against a test database!
VITE_RUN_DESTRUCTIVE_TESTS=false
```

#### 2. API Server Setup

Ensure your API server is running before executing integration tests:

```bash
# Example: Laravel development server
php artisan serve

# Example: Node.js server
npm run dev

# Example: Docker container
docker-compose up api
```

#### 3. Test Database Setup

**CRITICAL**: Always use a dedicated test database!

```bash
# Example: Laravel test environment
cp .env .env.testing
# Edit .env.testing to use test database
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

### Integration Test Features

#### Read-Only Tests (Always Safe)
- ✅ Get all countries, languages, contexts, etc.
- ✅ Authentication status checks  
- ✅ Error handling verification
- ✅ API endpoint accessibility

#### CRUD Tests (Destructive Mode Only)
- 🔥 Create new records for all entities
- 🔥 Update existing records
- 🔥 Delete test records
- 🔥 Test validation errors
- 🔥 Test authentication flows

#### Safety Features
- Sequential test execution (no conflicts)
- Automatic cleanup of created test data
- Environment variable controls
- Interactive confirmation prompts
- Separate test configuration

### Integration Test Example

```typescript
describe('Countries API Integration', () => {
  test('should create, read, update, and delete a country', async () => {
    // Skip if destructive tests are disabled
    if (!TEST_CONFIG.RUN_DESTRUCTIVE_TESTS) {
      return
    }

    // Create
    const newCountry = await apiClient.createCountry({
      name: 'Test Country',
      iso_code: 'TC'
    })
    createdResources.countries.push(newCountry.id)

    // Read
    const fetchedCountry = await apiClient.getCountry(newCountry.id)
    expect(fetchedCountry.name).toBe('Test Country')

    // Update
    const updatedCountry = await apiClient.updateCountry(newCountry.id, {
      name: 'Updated Test Country'
    })
    expect(updatedCountry.name).toBe('Updated Test Country')

    // Delete
    await apiClient.deleteCountry(newCountry.id)
    await expect(apiClient.getCountry(newCountry.id)).rejects.toThrow()
  })
})
```

## Test Configuration

### Unit Test Configuration

File: `vitest.config.ts`

```typescript
export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['src/test/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'src/test/',
        '**/*.d.ts',
        '**/*.config.*'
      ],
      thresholds: {
        global: {
          branches: 80,
          functions: 85,
          lines: 85,
          statements: 85
        }
      }
    }
  }
})
```

### Integration Test Configuration

File: `vitest.integration.config.ts`

```typescript
export default defineConfig({
  plugins: [vue()],
  test: {
    name: 'integration',
    include: ['**/integration.test.ts'],
    environment: 'jsdom',
    globals: true,
    // Run tests sequentially to avoid conflicts
    pool: 'forks',
    poolOptions: {
      forks: { singleFork: true }
    },
    // Longer timeout for network requests
    testTimeout: 30000,
    reporters: ['verbose']
  }
})
```

## Continuous Integration

### GitHub Actions Workflow

The project includes automated testing in CI/CD:

```yaml
# .github/workflows/ci.yml
name: CI
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'
      
      - run: npm ci
      - run: npm run type-check
      - run: npm run lint
      - run: npm run test:coverage
      - run: npm run build
```

### Integration Tests in CI

Integration tests are **NOT** run automatically in CI/CD to avoid:
- Requiring live API servers in CI environment
- Potential data modification in shared environments
- Network dependency failures
- Slower build times

To run integration tests in CI (if needed):

```yaml
integration-test:
  runs-on: ubuntu-latest
  services:
    api:
      image: your-api-image
      ports:
        - 8000:8000
  steps:
    - uses: actions/checkout@v4
    - uses: actions/setup-node@v4
    - run: npm ci
    - run: npm run test:integration
      env:
        VITE_API_BASE_URL: http://localhost:8000/api
        VITE_TEST_EMAIL: test@example.com
        VITE_TEST_PASSWORD: password123
```

## Test Scripts Reference

| Script | Purpose | When to Use |
|--------|---------|-------------|
| `npm test` | Run unit tests once | CI/CD, pre-commit |
| `npm run test:watch` | Unit tests in watch mode | Development |
| `npm run test:coverage` | Unit tests with coverage | Coverage reports |
| `npm run test:ui` | Interactive test UI | Development |
| `npm run test:integration:guided` | Guided integration runner | First-time integration testing |
| `npm run test:integration` | Read-only integration tests | Safe API verification |
| `npm run test:integration:destructive` | Full CRUD integration tests | Complete API testing (test DB only) |
| `npm run test:integration:watch` | Integration tests in watch mode | Integration test development |

## Troubleshooting

### Common Unit Test Issues

#### Mock Issues
```bash
# Clear mock cache
rm -rf node_modules/.vitest
npm test
```

#### Component Mount Failures
```typescript
// Provide required props/mocks
const wrapper = mount(Component, {
  props: { required: 'value' },
  global: {
    mocks: { $router: mockRouter }
  }
})
```

### Common Integration Test Issues

#### API Server Not Running
```bash
Error: API server is not accessible
```
**Solution**: Start your API server and verify the URL in `.env.integration.local`

#### Authentication Failures
```bash
Error: Request failed with status code 401
```
**Solution**: 
1. Verify test user credentials exist in your API
2. Check if authentication endpoints are correct
3. Ensure test user has required permissions

#### Database Issues
```bash
Error: Database connection failed
```
**Solution**:
1. Verify test database exists and is accessible
2. Run migrations: `php artisan migrate --env=testing`
3. Seed test data: `php artisan db:seed --env=testing`

#### Environment Configuration
```bash
Error: VITE_API_BASE_URL is not defined
```
**Solution**:
1. Create `.env.integration.local` file
2. Copy settings from `.env.integration`
3. Customize for your environment

### Test Data Cleanup

If integration tests fail and leave test data:

```bash
# Manual cleanup (if auto-cleanup fails)
# Connect to your test database and remove test records
# Look for records with names containing "Test" or "Integration"

# Reset test database (nuclear option)
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

### Performance Issues

#### Slow Unit Tests
```typescript
// Use vi.mock() to mock heavy dependencies
vi.mock('@/api/client')
vi.mock('axios')
```

#### Slow Integration Tests
```bash
# Run specific test suites only
npm run test:integration -- --grep "Countries"

# Use read-only mode for faster feedback
npm run test:integration
```

## Best Practices

### Unit Testing
1. **Mock external dependencies** (API calls, file system, etc.)
2. **Test behavior, not implementation** details
3. **Use descriptive test names** that explain the expected behavior
4. **Group related tests** with `describe` blocks
5. **Keep tests isolated** - each test should be independent

### Integration Testing
1. **Always use test databases** - never production data
2. **Run destructive tests sparingly** - they're slower and riskier
3. **Use the guided runner** for safety and convenience
4. **Clean up test data** - don't leave orphaned records
5. **Test realistic scenarios** - use actual data structures

### General Testing
1. **Write tests first** when fixing bugs
2. **Maintain test coverage** above minimum thresholds
3. **Run tests before committing** code changes
4. **Document complex test scenarios** with comments
5. **Use TypeScript** for type safety in tests

## Getting Help

- **Unit Test Issues**: Check existing tests in similar components
- **Integration Test Setup**: See `docs/integration-testing.md`
- **CI/CD Issues**: Check GitHub Actions logs
- **API Issues**: Verify API documentation and endpoints
- **Coverage Issues**: Use `npm run test:coverage` for detailed reports

---

For more detailed information, see:
- [Integration Testing Guide](integration-testing)
- [Contributing Guide](../contributing)
- [API Integration Documentation](api-integration)
