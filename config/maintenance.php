<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public Lock File
    |--------------------------------------------------------------------------
    |
    | This file is created in the public directory when the application enters
    | maintenance mode, allowing the SPA frontend to detect maintenance state
    | without requiring authentication.
    |
    */

    'public_lock_file' => env('MAINTENANCE_PUBLIC_LOCK_FILE', 'down.lock'),

    /*
    |--------------------------------------------------------------------------
    | Public Lock File Disk
    |--------------------------------------------------------------------------
    |
    | The disk configuration to use for storing the public lock file.
    | This should be configured in config/filesystems.php.
    |
    */

    'public_lock_disk' => env('MAINTENANCE_PUBLIC_LOCK_DISK', 'public-maintenance'),

];
