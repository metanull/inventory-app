---
layout: default
title: Integration Testing
nav_order: 1
parent: Testing
grand_parent: Guidelines
---

# Integration Testing Guide

This document explains how to run integration tests against a live API server.

## Overview

The integration test suite (`src/api/__tests__/integration.test.ts`) performs real HTTP requests against a live API server to test the complete integration between the frontend and backend. Unlike unit tests that use mocks, these tests verify actual API functionality.

## ⚠️ Important Warnings

- **Use only against test/development databases** - These tests create, modify, and delete real data
- **Never run against production** - Destructive tests will modify your production data
- **API server must be running** - Tests require a live API endpoint
- **Sequential execution** - Tests run sequentially to avoid data conflicts

## Setup

### 1. Environment Configuration

Create a `.env.integration.local` file (copy from `.env.integration`):

```bash
# API Configuration
VITE_API_BASE_URL=http://127.0.0.1:8000/api

# Test User Credentials (optional)
VITE_TEST_EMAIL=test@example.com
VITE_TEST_PASSWORD=password123

# Enable destructive tests (WARNING: Only for test databases!)
VITE_RUN_DESTRUCTIVE_TESTS=false
```

### 2. API Server Setup

Ensure your API server is running and accessible at the configured URL:

```bash
# Example: Start Laravel development server
php artisan serve
```

### 3. Test Database

**CRITICAL**: Use a dedicated test database, not your production database!

```bash
# Example: Configure Laravel for testing
cp .env .env.testing
# Edit .env.testing to use test database
DB_DATABASE=inventory_test
```

## Running Integration Tests

### Quick Start Commands

#### For PowerShell (Windows)

```powershell
# Read-only tests (safe for any environment)
$env:VITE_API_BASE_URL="http://127.0.0.1:8000/api"; $env:VITE_TEST_EMAIL="user@example.com"; $env:VITE_TEST_PASSWORD="password"; npx vitest run --config vitest.integration.config.ts

# Full CRUD tests (test database only!)
$env:VITE_API_BASE_URL="http://127.0.0.1:8000/api"; $env:VITE_TEST_EMAIL="user@example.com"; $env:VITE_TEST_PASSWORD="password"; $env:VITE_RUN_DESTRUCTIVE_TESTS="true"; npx vitest run --config vitest.integration.config.ts --reporter=verbose

# Watch mode for development
$env:VITE_API_BASE_URL="http://127.0.0.1:8000/api"; $env:VITE_TEST_EMAIL="user@example.com"; $env:VITE_TEST_PASSWORD="password"; npx vitest --config vitest.integration.config.ts
```

#### For Bash/Linux/macOS

```bash
# Read-only tests (safe for any environment)
VITE_API_BASE_URL="http://127.0.0.1:8000/api" VITE_TEST_EMAIL="user@example.com" VITE_TEST_PASSWORD="password" npx vitest run --config vitest.integration.config.ts

# Full CRUD tests (test database only!)
VITE_API_BASE_URL="http://127.0.0.1:8000/api" VITE_TEST_EMAIL="user@example.com" VITE_TEST_PASSWORD="password" VITE_RUN_DESTRUCTIVE_TESTS="true" npx vitest run --config vitest.integration.config.ts --reporter=verbose
```

### Using NPM Scripts

Alternatively, use the predefined npm scripts:

#### Read-Only Tests (Safe)

These tests only perform GET requests and are safe to run against any environment:

```bash
# Run read-only integration tests
npm run test:integration
```

#### Destructive Tests (Dangerous)

These tests create, update, and delete data. **Only run against test databases!**

```bash
# Run all integration tests including destructive operations
npm run test:integration:destructive
```

#### Watch Mode

Monitor integration tests during development:

```bash
# Run in watch mode (read-only by default)
npm run test:integration:watch
```

### Custom Configuration with NPX

Override environment variables for specific test runs using npx directly:

#### PowerShell Examples

```powershell
# Test against different API endpoint
$env:VITE_API_BASE_URL="http://localhost:3000/api"; $env:VITE_TEST_EMAIL="test@example.com"; $env:VITE_TEST_PASSWORD="password123"; npx vitest run --config vitest.integration.config.ts

# Test with different credentials
$env:VITE_API_BASE_URL="http://127.0.0.1:8000/api"; $env:VITE_TEST_EMAIL="admin@example.com"; $env:VITE_TEST_PASSWORD="admin123"; npx vitest run --config vitest.integration.config.ts

# Run only specific test suites
$env:VITE_API_BASE_URL="http://127.0.0.1:8000/api"; $env:VITE_TEST_EMAIL="user@example.com"; $env:VITE_TEST_PASSWORD="password"; npx vitest run --config vitest.integration.config.ts --grep "Countries"
```

#### Bash Examples

```bash
# Test against different API endpoint
VITE_API_BASE_URL="http://localhost:3000/api" VITE_TEST_EMAIL="test@example.com" VITE_TEST_PASSWORD="password123" npx vitest run --config vitest.integration.config.ts

# Enable destructive tests temporarily
VITE_RUN_DESTRUCTIVE_TESTS=true npm run test:integration
```

## Test Categories

### 1. Read-Only Tests

- **API Connectivity**: Verify API server is accessible
- **Data Retrieval**: Test GET endpoints for all entities
- **Authentication**: Test login/logout functionality
- **Error Handling**: Test 404 and validation error responses

### 2. CRUD Tests (Destructive)

When `VITE_RUN_DESTRUCTIVE_TESTS=true`, additional tests run:

- **Create**: Test POST endpoints for all entities
- **Update**: Test PUT/PATCH endpoints
- **Delete**: Test DELETE endpoints
- **Validation**: Test API validation rules
- **Relationships**: Test entity relationships

### 3. Error Handling Tests

- **404 Errors**: Non-existent resource handling
- **422 Validation**: Invalid data submission
- **401 Authentication**: Unauthorized access
- **Network Errors**: Connection failures

## Test Output

Integration tests provide detailed console output:

```
✅ API server is accessible
✅ Authentication successful
📊 Found 5 countries
✅ Country created: TST
✅ Country retrieved: TST
✅ Country updated: TST
✅ Country deleted: TST
🧹 Cleaning up created test resources...
```

## Troubleshooting

### API Server Not Accessible

```
❌ API server is not accessible: connect ECONNREFUSED
```

**Solution**: Ensure API server is running at the configured URL

### Authentication Fails

```
⚠️ Authentication failed, continuing with public endpoints only
```

**Solution**: Check credentials in `.env.integration.local`

### Validation Errors

```
422 Validation Error: The id field is required
```

**Solution**: Check API specification for required fields

### Database Constraint Errors

```
500 Internal Server Error: FOREIGN KEY constraint failed
```

**Solution**: Ensure test database is properly migrated and seeded

### PowerShell Environment Variable Issues

If npm scripts fail in PowerShell with environment variable errors:

```powershell
# Error: 'VITE_RUN_DESTRUCTIVE_TESTS' is not recognized as a command
```

**Solution**: Use npx directly with PowerShell environment variable syntax:

```powershell
# Instead of this (doesn't work in PowerShell):
npm run test:integration:destructive

# Use this (works correctly):
$env:VITE_API_BASE_URL="http://127.0.0.1:8000/api"; $env:VITE_TEST_EMAIL="user@example.com"; $env:VITE_TEST_PASSWORD="password"; $env:VITE_RUN_DESTRUCTIVE_TESTS="true"; npx vitest run --config vitest.integration.config.ts
```

### Vitest Not Found Error

```powershell
# Error: vitest: The term 'vitest' is not recognized
```

**Solution**: Use npx to run vitest:

```powershell
# Instead of:
vitest run --config vitest.integration.config.ts

# Use:
npx vitest run --config vitest.integration.config.ts
```

### Authentication Token Issues

If authentication works but subsequent requests fail with 401:

```
✅ Authentication successful
❌ Request failed with status code 401
```
