---
layout: default
title: Development Setup
parent: Deployment Guide
nav_order: 2
---

# Development Setup

{: .no_toc }

Complete guide for setting up a local development environment for the Inventory Management API.

## Table of Contents

{: .no_toc .text-delta }

1. TOC
   {:toc}

---

## Prerequisites

### System Requirements

| Component   | Minimum                                 | Recommended     |
| ----------- | --------------------------------------- | --------------- |
| **OS**      | Windows 10/11, macOS 12+, Ubuntu 20.04+ | Latest versions |
| **RAM**     | 8 GB                                    | 16 GB+          |
| **Storage** | 10 GB free space                        | 20 GB+ SSD      |

### Required Software

- **PHP 8.2+** with required extensions
- **Composer 2.0+**
- **Node.js 18+** and npm
- **Git**
- **VS Code** (recommended)

## Step 1: Install Prerequisites

### 1.1 PHP Installation

#### Windows

```powershell
# Option 1: Using Chocolatey
choco install php

# Option 2: Manual installation
# Download PHP 8.2+ from https://windows.php.net/
# Extract to C:\php and add to PATH
```

#### macOS

```bash
# Using Homebrew
brew install php@8.2
brew link php@8.2
```

#### Linux (Ubuntu/Debian)

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP and extensions
sudo apt install php8.2 php8.2-cli php8.2-curl php8.2-gd php8.2-mbstring \
                 php8.2-mysql php8.2-sqlite3 php8.2-xml php8.2-zip
```

### 1.2 Required PHP Extensions

Verify these extensions are enabled:

```bash
php -m | grep -E "(fileinfo|zip|sqlite3|pdo_sqlite|gd|exif|openssl|curl|mbstring)"
```

### 1.3 Composer Installation

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version
```

### 1.4 Node.js Installation

#### Windows

```powershell
# Option 1: Download from https://nodejs.org/
# Option 2: Using Chocolatey
choco install nodejs
```

#### macOS/Linux

```bash
# Using Node Version Manager (recommended)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
nvm use 18
```

## Step 2: Project Setup

### 2.1 Clone Repository

```bash
# Clone the repository
git clone https://github.com/metanull/inventory-app.git
cd inventory-app
```

### 2.2 Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2.3 Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2.4 Configure Environment

Edit `.env` file for development:

```env
# Application
APP_NAME="Inventory Management"
APP_ENV=local
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database (SQLite for development)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Image Storage (local development)
UPLOAD_IMAGES_DISK=local_upload_images
UPLOAD_IMAGES_PATH=uploads/images
AVAILABLE_IMAGES_DISK=local_available_images
AVAILABLE_IMAGES_PATH=available/images
PICTURES_DISK=local_pictures
PICTURES_PATH=pictures

# Cache
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (use log driver for development)
MAIL_MAILER=log

# Frontend Development
VITE_APP_URL=http://localhost:8000
```

### 2.5 Database Setup

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with test data
php artisan db:seed
```

### 2.6 Storage Setup

```bash
# Create symbolic link for storage
php artisan storage:link

# Create image storage directories
mkdir -p storage/app/uploads/images
mkdir -p storage/app/available/images
mkdir -p storage/app/pictures
```

## Step 3: Development Servers

### 3.1 Manual Start

Start both servers manually:

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite development server
npm run dev
```

### 3.2 Automated Start (Recommended)

Use the development script:

```powershell
# Start development servers
composer dev-start

# Start with database reset
composer dev-start -- --reset
```

The development script (`scripts/Start-Dev.ps1`) will:

- ✅ Start PHP artisan serve (Laravel API)
- ✅ Start npm run dev (Vite frontend server)
- ✅ Optionally reset database (delete + migrate + seed)
- ✅ Run both servers concurrently
- ✅ Handle graceful shutdown

## Step 4: IDE Configuration

### 4.1 VS Code Setup

Install recommended extensions:

```json
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",
    "Vue.volar",
    "bradlc.vscode-tailwindcss",
    "ryannaddy.laravel-artisan",
    "onecentlin.laravel-blade",
    "mikestead.dotenv"
  ]
}
```

### 4.2 VS Code Settings

```json
{
  "php.validate.executablePath": "/path/to/php",
  "intelephense.files.maxSize": 3000000,
  "vetur.validation.template": false,
  "vetur.validation.script": false,
  "vetur.validation.style": false
}
```

### 4.3 Debug Configuration

Create `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Launch Chrome",
      "request": "launch",
      "type": "pwa-chrome",
      "url": "http://localhost:8000",
      "webRoot": "${workspaceFolder}/resources/js"
    },
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    }
  ]
}
```

## Step 5: Development Workflow

### 5.1 Daily Development

```bash
# Start development environment
composer dev-start

# Access applications:
# - API: http://localhost:8000/api
# - Frontend: http://localhost:8000 (served by Laravel with Vite HMR)
# - Vite Dev Server: http://localhost:5173 (for direct asset access)
```

### 5.2 Code Quality

```bash
# Run tests
composer ci-test

# Check code style
composer ci-lint

# Fix code style
./vendor/bin/pint

# Run all quality checks
composer ci-before:pull-request
```

### 5.3 Database Management

```bash
# Reset database
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_example_table

# Create new model with factory and seeder
php artisan make:model Example -mfs

# Run specific seeder
php artisan db:seed --class=ExampleSeeder
```

### 5.4 Frontend Development

```bash
# Install new frontend dependency
npm install package-name

# Run frontend tests
npm run test

# Run integration tests
npm run test:integration

# Build for production
npm run build
```

## Step 6: Testing Environment

### 6.1 Unit Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter ExampleTest
```

### 6.2 Frontend Testing

```bash
# Run Vue.js unit tests
npm run test

# Run with watch mode
npm run test:watch

# Run integration tests
npm run test:integration
```

### 6.3 API Testing

```bash
# Test API endpoints
curl -X GET http://localhost:8000/api/projects \
     -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN"
```

## Step 7: Common Development Tasks

### 7.1 Creating New Features

```bash
# 1. Create migration
php artisan make:migration create_feature_table

# 2. Create model with factory and seeder
php artisan make:model Feature -mfs

# 3. Create controller
php artisan make:controller FeatureController --api

# 4. Create resource
php artisan make:resource FeatureResource

# 5. Add routes to routes/api.php
# 6. Create tests
php artisan make:test FeatureTest
```

### 7.2 Database Seeding

```bash
# Create seeder
php artisan make:seeder FeatureSeeder

# Add to DatabaseSeeder.php
# Run seeder
php artisan db:seed --class=FeatureSeeder
```

### 7.3 Frontend Components

```bash
# Create new Vue component in resources/js/components/
# Add to router in resources/js/router/index.ts
# Create tests in resources/js/components/__tests__/
```

## Step 8: Troubleshooting

### 8.1 Common Issues

#### Port Already in Use

```bash
# Kill process using port 8000
lsof -ti:8000 | xargs kill -9

# Or use different port
php artisan serve --port=8001
```

#### Permission Issues (Linux/macOS)

```bash
# Fix storage permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### Composer Issues

```bash
# Clear Composer cache
composer clear-cache

# Update dependencies
composer update

# Dump autoload
composer dump-autoload
```

#### NPM Issues

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### 8.2 Performance Issues

#### Slow Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# Disable Xdebug when not debugging
# Comment out xdebug extension in php.ini
```

#### Slow Frontend Compilation

```bash
# Use Vite optimization
npm run dev -- --host

# Increase Node.js memory limit
export NODE_OPTIONS="--max-old-space-size=4096"
```

## Step 9: Development Scripts

### 9.1 Start-Dev.ps1 Script

The project includes a PowerShell script for easy development:

```powershell
# Basic start
.\scripts\Start-Dev.ps1

# Start with database reset
.\scripts\Start-Dev.ps1 -Reset

# Start with custom ports
.\scripts\Start-Dev.ps1 -LaravelPort 8001 -VitePort 5174
```

### 9.2 Composer Scripts

Available composer scripts for development:

```bash
# Start development environment
composer dev-start

# Start with reset
composer dev-start -- --reset

# Quality checks
composer ci-lint
composer ci-test
composer ci-before:pull-request
```

## Step 10: Git Workflow

### 10.1 Branch Management

```bash
# Create feature branch
git checkout -b feature/new-feature

# Commit changes
git add .
git commit -m "feat: add new feature"

# Push branch
git push origin feature/new-feature
```

### 10.2 Pre-commit Checks

```bash
# Run before committing
composer ci-before:pull-request

# This runs:
# - Code formatting (Pint)
# - Tests (PHPUnit)
# - Security audit
# - OpenAPI documentation generation
```

---

## Next Steps

- 📖 [Production Deployment](production-deployment) - Deploy to production
- 🔧 [Configuration](configuration) - Advanced configuration options
- 🧪 [Testing Guide](testing) - Comprehensive testing strategies
- 📊 [API Documentation](../api-documentation) - API endpoint documentation
