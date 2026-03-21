---
layout: default
title: Deployment Guide
nav_order: 3
has_children: true
permalink: /deployment/
---

# Deployment Guide

{: .no_toc }

This section covers how to set up the system for local development and how to deploy it to a production server.

## Table of Contents

{: .no_toc .text-delta }

1. TOC
   {:toc}

---

## Overview

The system can be deployed in several configurations:

- **Development** — Local machine with SQLite and the built-in PHP server
- **Production** — Windows Server with Apache and MariaDB
- **Testing** — Same as development, using an isolated SQLite database

## Quick Start (Development)

```powershell
git clone https://github.com/metanull/inventory-app.git
cd inventory-app
composer install
npm install
cp .env.example .env
php artisan key:generate
composer dev
```

This starts the Laravel server, asset watcher, and queue worker. Access the web interface at `http://localhost:8000/web`.

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Server    │    │    Laravel      │    │    Database     │
│  (Apache/Nginx) │◄──►│   Application   │◄──►│   (MariaDB)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │
         ▼                       ▼
    Static Assets          Business Logic
    CSS/JS                 Web Interface (Blade/Livewire)
    Uploaded Images        REST API (/api routes)
                           Authentication & Permissions
```

The **web interface** (Blade/Livewire, server-rendered) is the primary production UI. A Vue.js SPA demo also exists as a reference for external API consumers, but is not part of the core deployment.

## Security

- Token-based API authentication (Laravel Sanctum)
- Role-based access control (Spatie Permissions)
- HTTPS, CSRF protection, and security headers
- Input validation on all endpoints
- SQL injection prevention via Eloquent ORM

---

## Next Steps

- [Development Setup](development-setup) — Set up a local environment
- [Production Deployment](production-deployment) — Deploy to a Windows Server
- [Configuration](configuration) — Environment and application settings
- [Server Configuration](server-configuration) — Apache/Nginx setup
- [Trusted Proxy Configuration](trusted-proxies) — Reverse proxy support
- [Command Line User Management](command-line-user-management) — Manage users via CLI
- [CORS Configuration](cors-configuration) — Cross-origin request settings
- [Testing Troubleshooting](testing-troubleshooting) — Common testing issues
