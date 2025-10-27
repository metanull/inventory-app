@props([
    'sortable' => false,
    'hidden' => '',
])

@php
    $baseClass = 'px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
    $hiddenClass = $hidden;
@endphp

<th scope="col" {{ $attributes->merge(['class' => $baseClass . ' ' . $hiddenClass]) }}>
    {{ $slot }}
</th>
