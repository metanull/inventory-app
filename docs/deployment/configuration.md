---
layout: default
title: Configuration
parent: Deployment Guide
nav_order: 3
---

# Configuration Guide

{: .no_toc }

Comprehensive configuration guide for the Inventory Management API covering environment variables, application settings, and advanced configurations.

## Table of Contents

{: .no_toc .text-delta }

1. TOC
   {:toc}

---

## Environment Configuration

### Core Application Settings

```env
# Application Identity
APP_NAME="Inventory Management"
APP_ENV=production                    # local, testing, staging, production
APP_KEY=base64:GENERATED_KEY         # Generated with php artisan key:generate
APP_DEBUG=false                      # true for development, false for production
APP_TIMEZONE=UTC                     # Application timezone
APP_URL=https://your-domain.com      # Full application URL
APP_LOCALE=en                        # Default application locale
APP_FALLBACK_LOCALE=en              # Fallback locale
```

### Database Configuration

#### Production (MariaDB/MySQL)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_production
DB_USERNAME=inventory_user
DB_PASSWORD=secure_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

#### Development (SQLite)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
DB_FOREIGN_KEYS=true
```

#### Testing (In-memory SQLite)

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Storage Configuration

#### Local Storage (Default)

```env
# Upload Images (user uploads)
UPLOAD_IMAGES_DISK=local_upload_images
UPLOAD_IMAGES_PATH=uploads/images

# Available Images (processed/optimized)
AVAILABLE_IMAGES_DISK=local_available_images
AVAILABLE_IMAGES_PATH=available/images

# Pictures (final processed images)
PICTURES_DISK=local_pictures
PICTURES_PATH=pictures
```

#### AWS S3 Storage

```env
# S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=inventory-app-storage
AWS_USE_PATH_STYLE_ENDPOINT=false

# Storage Disks
UPLOAD_IMAGES_DISK=s3_upload_images
UPLOAD_IMAGES_PATH=uploads/images

AVAILABLE_IMAGES_DISK=s3_available_images
AVAILABLE_IMAGES_PATH=available/images

PICTURES_DISK=s3_pictures
PICTURES_PATH=pictures
```

### Caching Configuration

#### File Cache (Development)

```env
CACHE_STORE=file
CACHE_PREFIX=inventory_app
```

#### Redis Cache (Production)

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

### Session Configuration

#### File Sessions (Default)

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true          # true for HTTPS
SESSION_SAME_SITE=strict
```

#### Database Sessions

```env
SESSION_DRIVER=database
SESSION_CONNECTION=mysql
SESSION_TABLE=sessions
```

### Queue Configuration

#### Sync Queue (Development)

```env
QUEUE_CONNECTION=sync
```

#### Database Queue (Production)

```env
QUEUE_CONNECTION=database
QUEUE_TABLE=jobs
QUEUE_RETRY_AFTER=90
QUEUE_FAILED_TABLE=failed_jobs
```

#### Redis Queue (High Performance)

```env
QUEUE_CONNECTION=redis
REDIS_QUEUE_HOST=127.0.0.1
REDIS_QUEUE_PORT=6379
REDIS_QUEUE_DB=1
```

### Mail Configuration

#### SMTP (Production)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Inventory Management"
```

#### Log (Development)

```env
MAIL_MAILER=log
```

### Authentication Configuration

```env
# Sanctum (API Authentication)
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,your-frontend-domain.com
SANCTUM_GUARD=web
SANCTUM_MIDDLEWARE=web

# JWT Configuration (if using JWT)
JWT_SECRET=your-jwt-secret
JWT_TTL=60                          # Token TTL in minutes
JWT_REFRESH_TTL=20160              # Refresh token TTL in minutes
```

### Logging Configuration

```env
LOG_CHANNEL=daily                   # single, daily, slack, papertrail
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error                     # emergency, alert, critical, error, warning, notice, info, debug
LOG_DAILY_DAYS=14                   # Days to keep daily logs
```

### Security Configuration

```env
# CSRF Protection
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,PATCH,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With
CORS_EXPOSED_HEADERS=
CORS_MAX_AGE=0
CORS_SUPPORTS_CREDENTIALS=true
```

## Application Configuration Files

### config/app.php

Key configuration options:

```php
<?php

return [
    'name' => env('APP_NAME', 'Inventory Management'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    // Key configuration for encryption
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    // Providers configuration
    'providers' => [
        // Laravel Framework Service Providers
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        // ... other providers

        // Application Service Providers
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],
];
```

### config/database.php

Database connections configuration:

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
];
```

### config/filesystems.php

Storage configuration:

```php
<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Custom storage disks for image management
        'local_upload_images' => [
            'driver' => 'local',
            'root' => storage_path('app/'.env('UPLOAD_IMAGES_PATH', 'uploads/images')),
            'url' => env('APP_URL').'/storage/'.env('UPLOAD_IMAGES_PATH', 'uploads/images'),
            'visibility' => 'public',
        ],

        'local_available_images' => [
            'driver' => 'local',
            'root' => storage_path('app/'.env('AVAILABLE_IMAGES_PATH', 'available/images')),
            'url' => env('APP_URL').'/storage/'.env('AVAILABLE_IMAGES_PATH', 'available/images'),
            'visibility' => 'public',
        ],

        'local_pictures' => [
            'driver' => 'local',
            'root' => storage_path('app/'.env('PICTURES_PATH', 'pictures')),
            'url' => env('APP_URL').'/storage/'.env('PICTURES_PATH', 'pictures'),
            'visibility' => 'public',
        ],
    ],
];
```

### config/sanctum.php

API authentication configuration:

```php
<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    'guard' => env('SANCTUM_GUARD', 'web'),
    'expiration' => null,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];
```

## Environment-Specific Configurations

### Development Environment

```env
# .env.local
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log

LOG_CHANNEL=single
LOG_LEVEL=debug

# Frontend development
VITE_APP_URL=http://localhost:8000
```

### Testing Environment

```env
# .env.testing
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:TEST_KEY

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array

LOG_CHANNEL=single
LOG_LEVEL=debug
```

### Staging Environment

```env
# .env.staging
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.your-domain.com

DB_CONNECTION=mysql
DB_HOST=staging-db.your-domain.com
DB_DATABASE=inventory_staging

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
LOG_CHANNEL=daily
LOG_LEVEL=info
```

### Production Environment

```env
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=production-db.your-domain.com
DB_DATABASE=inventory_production

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
LOG_CHANNEL=daily
LOG_LEVEL=error

# Security settings
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

## Performance Configuration

### PHP Configuration (php.ini)

```ini
; Memory and execution limits
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000

; File upload settings
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 20

; OPcache configuration
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

### Database Configuration

#### MySQL/MariaDB Configuration

```ini
; my.cnf / my.ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 128M
max_connections = 200
```

### Redis Configuration

```conf
# redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## Security Configuration

### Web Server Security Headers

#### Apache (.htaccess)

```apache
# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

# Content Security Policy
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'"
```

#### Nginx

```nginx
# Security headers
add_header X-Content-Type-Options nosniff always;
add_header X-Frame-Options DENY always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
```

### Laravel Security Configuration

```php
// config/cors.php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

## Monitoring Configuration

### Laravel Telescope (Development)

```env
TELESCOPE_ENABLED=true
TELESCOPE_PATH=telescope
```

### Laravel Horizon (Queue Monitoring)

```env
HORIZON_PATH=horizon
HORIZON_ENVIRONMENT=production
```

### Log Configuration for Monitoring

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],

    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => env('LOG_LEVEL', 'critical'),
    ],
],
```

---

## Configuration Validation

### Environment Validation

Create a configuration validation command:

```bash
php artisan make:command ValidateConfiguration
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ValidateConfiguration extends Command
{
    protected $signature = 'config:validate';
    protected $description = 'Validate application configuration';

    public function handle()
    {
        $this->info('Validating application configuration...');

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection: FAILED');
            $this->error($e->getMessage());
        }

        // Check storage disks
        $disks = ['local_upload_images', 'local_available_images', 'local_pictures'];
        foreach ($disks as $disk) {
            try {
                Storage::disk($disk)->exists('test');
                $this->info("✅ Storage disk '{$disk}': OK");
            } catch (\Exception $e) {
                $this->error("❌ Storage disk '{$disk}': FAILED");
                $this->error($e->getMessage());
            }
        }

        // Check required environment variables
        $required = ['APP_KEY', 'DB_CONNECTION', 'APP_URL'];
        foreach ($required as $env) {
            if (env($env)) {
                $this->info("✅ Environment variable '{$env}': OK");
            } else {
                $this->error("❌ Environment variable '{$env}': MISSING");
            }
        }

        $this->info('Configuration validation complete.');
    }
}
```

---

## Next Steps

- 🚀 [Server Configuration](server-configuration) - Web server setup
- 💻 [Development Setup](development-setup) - Local development
- 📊 [Monitoring](monitoring) - Application monitoring
- 🔐 [Security](security) - Security best practices
