@props([
    'name' => 'q',
    'placeholder' => 'Search...',
    'clearable' => true,
])

@php
    $currentValue = request()->query($name, '');
    $clearUrl = request()->fullUrlWithQuery([$name => '', 'page' => 1]);
    $existingParams = request()->except($name, 'page');
@endphp

<form method="GET" class="flex flex-wrap items-center gap-3">
    @foreach($existingParams as $paramName => $paramValue)
        @if(is_array($paramValue))
            @foreach($paramValue as $k => $v)
                <input type="hidden" name="{{ $paramName }}[{{ $k }}]" value="{{ $v }}" />
            @endforeach
        @elseif(!is_null($paramValue))
            <input type="hidden" name="{{ $paramName }}" value="{{ $paramValue }}" />
        @endif
    @endforeach

    <div class="relative">
        <input
            type="text"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $placeholder }}"
            class="w-64 rounded-md border-gray-300 {{ $c['focus'] ?? 'focus:border-indigo-500 focus:ring-indigo-500' }}"
            @input.debounce.300ms="$el.closest('form').requestSubmit()"
        />
    </div>

    @if($clearable && $currentValue !== '')
        <a href="{{ $clearUrl }}" class="text-sm text-gray-600 hover:underline">Clear</a>
    @endif

    {{ $slot }}
</form>
