---
layout: default
title: Deployment Guide
nav_order: 3
has_children: true
permalink: /deployment/
---

# Deployment Guide

{: .no_toc }

This guide covers comprehensive deployment instructions for the Inventory Management API, including production deployment, development setup, and local testing environments.

## Table of Contents

{: .no_toc .text-delta }

1. TOC
   {:toc}

---

## Overview

The Inventory Management API can be deployed in several configurations:

- **Production Deployment** - Windows Server with Apache/Nginx and MariaDB
- **Development Environment** - Local development with PHP artisan serve and Vite
- **Testing Environment** - Local testing with SQLite database

## Quick Start

### Development Environment

```powershell
# Clone the repository
git clone https://github.com/metanull/inventory-app.git
cd inventory-app

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Start development servers
composer dev
```

### Production Deployment

```powershell
# Use the automated deployment script
.\deployment\deploy-windows.ps1 -Domain "your-domain.com"
```

## Architecture Overview

The application follows a modern N-tier architecture:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Server    │    │  Laravel API    │    │    Database     │
│  (Apache/Nginx) │◄──►│   Application   │◄──►│   (MariaDB)     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
    Static Assets          Business Logic          Data Storage
    Vue.js Frontend        REST API Endpoints      Inventory Data
    Image Files            Authentication          Audit Logs
```

## Technology Stack

| Component           | Technology   | Version    | Purpose                 |
| ------------------- | ------------ | ---------- | ----------------------- |
| **Backend**         | PHP Laravel  | 12+        | REST API Framework      |
| **Frontend**        | Vue.js       | 3+         | Single Page Application |
| **Database**        | MariaDB      | 10.5+      | Production Database     |
| **Web Server**      | Apache/Nginx | 2.4+/1.18+ | HTTP Server             |
| **Build Tool**      | Vite         | 7+         | Asset Compilation       |
| **Package Manager** | Composer/NPM | Latest     | Dependency Management   |

## Security Considerations

- **Authentication** - Laravel Sanctum token-based authentication
- **Authorization** - Role-based access control
- **HTTPS** - SSL/TLS encryption for all communications
- **CSRF Protection** - Cross-site request forgery protection
- **Input Validation** - Comprehensive request validation
- **SQL Injection Prevention** - Eloquent ORM with prepared statements
- **Security Headers** - HSTS, CSP, and other security headers

## Performance Optimization

- **Caching** - Redis/File-based caching for API responses
- **Asset Optimization** - Minified and compressed CSS/JS
- **Database Indexing** - Optimized database queries
- **Image Optimization** - Compressed images with Laravel Intervention
- **CDN Ready** - Static assets can be served via CDN

---

## Next Steps

- [Development Setup](development-setup) - Local development environment
- [Configuration](configuration) - Environment and application configuration
- [Server Configuration](server-configuration) - Web server setup guides
- [Trusted Proxy Configuration](trusted-proxies) - Configure reverse proxy support
- [Command Line User Management](command-line-user-management) - Manage users and roles via CLI
