---
layout: default
title: Auto-Reload Feature
nav_order: 5
parent: Features
---

# Auto-Reload Feature

The inventory management SPA includes an automatic reload mechanism that detects backend updates, maintenance mode, and service interruptions, ensuring users always run the latest version with minimal disruption.

## Overview

The auto-reload feature implements a three-pronged detection approach:

1. **Version Detection**: Monitors `version.json` for build changes
2. **Maintenance Mode Detection**: Checks for `down.lock` file during deployments
3. **Service Availability**: Monitors API health status

## How It Works

### Version Detection

During the build process, a `version.json` file is generated containing:

```json
{
  "app_version": "1.0.0",
  "build_number": "42",
  "unique_build_id": "1.0.0.42",
  "api_client_version": "1.0.1",
  "commit_sha": "abc123...",
  "build_timestamp": "2024-11-06T12:00:00Z"
}
```

The SPA:
- Loads the initial version on app startup
- Checks for version updates on every API request (with 15-second cooldown)
- Automatically reloads when a version mismatch is detected

### Maintenance Mode Detection

Custom artisan commands extend Laravel's standard `down` and `up` commands:

```bash
php artisan down    # Creates public/down.lock
php artisan up      # Removes public/down.lock
```

When `down.lock` is detected:
- A maintenance overlay blocks all user interactions
- The app polls every 10 seconds for recovery
- Automatically reloads when maintenance mode ends

### Activity-Based Checking

Version checks piggyback on API requests through the `sessionAwareAxios` interceptor:
- Non-blocking checks (don't delay API calls)
- Singleton pattern prevents parallel checks
- 15-second cooldown prevents excessive polling
- Silent operation (no user notifications unless action needed)

## User Experience

### Normal Operation
- Version checking happens silently in the background
- No impact on user workflow
- No performance overhead

### Update Detected
1. Maintenance overlay appears with update message
2. Short delay (2 seconds) to inform user
3. Automatic page reload
4. Fresh application state with latest version

### Maintenance Mode
1. Overlay appears with maintenance message
2. All interactions blocked
3. Automatic polling for recovery
4. Reload when service restored

## Technical Details

### Frontend Components

**Store**: `useVersionCheckStore` (Pinia)
- Manages version state
- Implements cooldown logic
- Provides singleton checking pattern

**Interceptor**: `sessionAwareAxios`
- Integrates version checking with API calls
- Non-blocking implementation
- Respects cooldown period

**Component**: `MaintenanceOverlay.vue`
- Modal overlay with full-screen blocking
- Dynamic messaging based on state
- Auto-reload triggers

### Backend Components

**Commands**:
- `CustomDownCommand`: Extends Laravel's down command
- `CustomUpCommand`: Extends Laravel's up command

**Files**:
- `public/version.json`: Version information
- `public/down.lock`: Maintenance mode indicator

### Build Process

The GitHub Actions build workflow:
1. Generates `VERSION` file with build metadata
2. Copies `VERSION` to `public/version.json`
3. Deploys to production

The deployment workflow:
1. Runs `php artisan down` (creates `down.lock`)
2. Swaps deployment symlinks
3. Runs migrations and configuration
4. Runs `php artisan up` (removes `down.lock`)

## Configuration

### Cooldown Period

Adjust the cooldown in `versionCheck.ts`:

```typescript
// Default: 15 seconds (15000ms)
const COOLDOWN_PERIOD = 15000
```

### Recovery Polling Interval

Adjust polling in `MaintenanceOverlay.vue`:

```typescript
// Default: 10 seconds (10000ms)
recoveryCheckInterval = window.setInterval(() => {
  versionStore.checkVersion()
}, 10000)
```

## Testing

### PHP Tests
```bash
php artisan test --filter=CustomDownCommandTest
php artisan test --filter=CustomUpCommandTest
```

### TypeScript Tests
```bash
npm run test -- versionCheck.test.ts
```

## Troubleshooting

### Version Check Not Triggering

**Symptom**: App doesn't reload after deployment

**Possible Causes**:
- Build workflow didn't copy `version.json`
- Version file not accessible (check permissions)
- Cooldown period too long
- Browser caching `version.json`

**Solutions**:
- Verify `public/version.json` exists after build
- Check file permissions
- Clear browser cache
- Add cache-busting query parameter (already implemented)

### Maintenance Overlay Stuck

**Symptom**: Overlay doesn't disappear after `artisan up`

**Possible Causes**:
- `down.lock` file not removed
- File permission issues
- Polling interval too long

**Solutions**:
- Manually remove `public/down.lock`
- Check file permissions
- Reload page manually

### False Maintenance Detection

**Symptom**: Overlay appears when not in maintenance

**Possible Causes**:
- Orphaned `down.lock` file
- Deployment failure left file behind

**Solutions**:
- Remove `public/down.lock` manually
- Run `php artisan up` to clean up

## Security Considerations

- `version.json` contains only public build metadata
- No sensitive information exposed
- `down.lock` contains only timestamp and message
- All checks use cache-busting parameters
- No authentication required for version/lock files

## Performance Impact

- **Negligible**: Checks piggyback on existing API calls
- **Cooldown**: 15-second minimum between checks
- **Non-blocking**: No delay to API requests
- **Efficient**: Singleton pattern prevents parallel checks
- **Small files**: version.json (~500 bytes), down.lock (~150 bytes)

## Future Enhancements

Potential improvements:
- WebSocket-based notifications (eliminate polling)
- Service worker integration for offline detection
- Progressive web app (PWA) support
- Configurable user preferences (notification style)
- Release notes display on update
