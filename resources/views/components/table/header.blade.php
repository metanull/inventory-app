@props(['class' => 'bg-gray-50'])

<thead {{ $attributes->merge(['class' => $class]) }}>
    <tr>
        {{ $slot }}
    </tr>
</thead>
