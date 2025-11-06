<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Interface Coexistence Flags
    |--------------------------------------------------------------------------
    |
    | The application exposes both a server-rendered Blade interface and a
    | Vue.js SPA demo client. These flags control visibility of navigation
    | links allowing users to switch between them. They are environment
    | configurable to support deployments that only expose one surface.
    |
    */
    'show_spa_link' => env('SHOW_SPA_LINK', true),
    'show_blade_link' => env('SHOW_BLADE_LINK', true),

    /*
    |--------------------------------------------------------------------------
    | SPA URL Configuration
    |--------------------------------------------------------------------------
    |
    | In development (composer dev), the SPA runs on Vite dev server with HMR.
    | In production, Laravel serves the built SPA from /cli route.
    | Set SPA_URL to the appropriate URL for your environment.
    |
    */
    'spa_url' => env('SPA_URL', env('APP_ENV') === 'local' ? 'http://127.0.0.1:5174/cli/' : url('/cli')),

    /*
    | Maximum page size exposed to both interfaces. Keep in sync with
    | frontend constants; consider exporting via an artisan command if
    | the SPA needs this value at build time.
    */
    'pagination' => [
        'default_per_page' => env('WEB_DEFAULT_PER_PAGE', 10),
        'max_per_page' => env('WEB_MAX_PER_PAGE', 100),
        'per_page_options' => [10, 20, 25, 50, 100],
    ],
];
