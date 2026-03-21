---
layout: default
title: Auto-Reload
nav_order: 5
---

# Auto-Reload

The system automatically detects when a new version has been deployed or when the application enters maintenance mode, and refreshes the browser so that users are always running the latest version.

## What Users See

### During normal operation

Nothing — version checking happens silently in the background, piggybacking on regular API requests. There is no performance impact and no visible indicator.

### When a new version is deployed

1. A full-screen overlay appears informing the user that an update is available.
2. After a short delay, the page reloads automatically with the latest version.

### When the system is under maintenance

1. A full-screen overlay appears with a maintenance message. All interactions are blocked.
2. The application polls for recovery every 10 seconds.
3. Once maintenance ends, the page reloads automatically.

## How It Works

The feature relies on two small files served from the web root:

- **`version.json`** — generated during the build process, contains the application version and build identifier. The SPA compares this to the version it loaded at startup; a mismatch triggers a reload.
- **`down.lock`** — created by `php artisan down` and removed by `php artisan up`. Its presence signals that the application is in maintenance mode.

Version checks are triggered on every API request but are rate-limited (15-second cooldown) and non-blocking — they never delay the user's action.
