<?php

return [
    // Base tailwind color names used in SPA (see resources/js/composables/useColors.ts)
    'colors' => [
        'items' => 'teal',
        'item_translations' => 'teal',
        'partners' => 'yellow',
        'countries' => 'indigo',
        'languages' => 'fuchsia',
        // New entities mapped to existing fragments for consistency
        'projects' => 'teal',
        'contexts' => 'indigo',
        'collections' => 'yellow',
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
            'base' => 'teal-500',
            'bg' => 'bg-teal-50',
            'text' => 'text-teal-600',
        ],
        'yellow' => [
            'button' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
            'focus' => 'focus:border-yellow-500 focus:ring-yellow-500',
            'badge' => 'bg-yellow-100 text-yellow-700',
            'accentText' => 'text-yellow-700',
            'accentLink' => 'text-yellow-600 hover:text-yellow-800',
            'pill' => 'bg-yellow-100 text-yellow-600',
            'base' => 'yellow-500',
            'bg' => 'bg-yellow-50',
            'text' => 'text-yellow-600',
        ],
        'indigo' => [
            'button' => 'bg-indigo-600 hover:bg-indigo-700 text-white',
            'focus' => 'focus:border-indigo-500 focus:ring-indigo-500',
            'badge' => 'bg-indigo-100 text-indigo-700',
            'accentText' => 'text-indigo-700',
            'accentLink' => 'text-indigo-600 hover:text-indigo-800',
            'pill' => 'bg-indigo-100 text-indigo-600',
            'base' => 'indigo-500',
            'bg' => 'bg-indigo-50',
            'text' => 'text-indigo-600',
        ],
        'fuchsia' => [
            'button' => 'bg-fuchsia-600 hover:bg-fuchsia-700 text-white',
            'focus' => 'focus:border-fuchsia-500 focus:ring-fuchsia-500',
            'badge' => 'bg-fuchsia-100 text-fuchsia-700',
            'accentText' => 'text-fuchsia-700',
            'accentLink' => 'text-fuchsia-600 hover:text-fuchsia-800',
            'pill' => 'bg-fuchsia-100 text-fuchsia-600',
            'base' => 'fuchsia-500',
            'bg' => 'bg-fuchsia-50',
            'text' => 'text-fuchsia-600',
        ],
    ],
];
