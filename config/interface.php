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
    | Maximum page size exposed to both interfaces. Keep in sync with
    | frontend constants; consider exporting via an artisan command if
    | the SPA needs this value at build time.
    */
    'pagination' => [
        'default_per_page' => env('WEB_DEFAULT_PER_PAGE', 20),
        'max_per_page' => env('WEB_MAX_PER_PAGE', 100),
        'per_page_options' => [10, 20, 50, 100],
    ],
];
