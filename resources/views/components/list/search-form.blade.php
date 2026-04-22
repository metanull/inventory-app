@props([
    'action',
    'query' => [],
    'search' => null,
    'searchParam' => 'q',
    'placeholder' => 'Search...',
    'submitLabel' => 'Search',
    'clearUrl' => null,
])

@php
    $renderHidden = function (string $name, mixed $value) use (&$renderHidden): void {
        if (is_array($value)) {
            foreach ($value as $key => $nestedValue) {
                $renderHidden($name.'['.$key.']', $nestedValue);
            }

            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        echo '<input type="hidden" name="'.e($name).'" value="'.e((string) $value).'" />';
    };
@endphp

<form method="GET" action="{{ $action }}" {{ $attributes->class('flex flex-wrap items-center gap-3') }}>
    @foreach($query as $key => $value)
        @php($renderHidden($key, $value))
    @endforeach

    <div class="relative flex-1 min-w-[16rem]">
        <input
            type="text"
            name="{{ $searchParam }}"
            value="{{ $search }}"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        />
    </div>

    {{ $slot }}

    <div class="flex items-center gap-2">
        <x-ui.button type="submit" variant="primary" size="sm">
            {{ $submitLabel }}
        </x-ui.button>

        @if($clearUrl)
            <x-ui.button :href="$clearUrl" variant="secondary" size="sm">
                Clear
            </x-ui.button>
        @endif
    </div>
</form>