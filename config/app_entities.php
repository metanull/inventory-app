<?php

return [
    // Base tailwind color names used in SPA (see resources/js/composables/useColors.ts)
    'colors' => [
        'items' => 'teal',
        'partners' => 'yellow',
    ],
    // Utility mapping for commonly used fragments in Blade (derived from COLOR_MAP in SPA)
    'fragments' => [
        'teal' => [
            'button' => 'bg-teal-600 hover:bg-teal-700 text-white',
            'focus' => 'focus:border-teal-500 focus:ring-teal-500',
            'badge' => 'bg-teal-100 text-teal-700',
            'accentText' => 'text-teal-700',
            'accentLink' => 'text-teal-600 hover:text-teal-800',
            'pill' => 'bg-teal-100 text-teal-600',
        ],
        'yellow' => [
            'button' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
            'focus' => 'focus:border-yellow-500 focus:ring-yellow-500',
            'badge' => 'bg-yellow-100 text-yellow-700',
            'accentText' => 'text-yellow-700',
            'accentLink' => 'text-yellow-600 hover:text-yellow-800',
            'pill' => 'bg-yellow-100 text-yellow-600',
        ],
    ],
];
