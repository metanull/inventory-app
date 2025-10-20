{{--
    UUID Display Component
    Displays UUIDs in short or long format with hover tooltip
    Based on resources/js/components/format/Uuid.vue
    
    Usage:
    <x-format.uuid :uuid="$model->id" format="short" />
    <x-format.uuid :uuid="$model->id" format="long" />
    <x-format.uuid :uuid="$model->id" format="short" :length="12" />
--}}

@props([
    'uuid' => null,
    'format' => 'short',  // 'short' | 'long'
    'length' => 8,        // Length for short format (default 8 like Git short hash)
    'className' => 'font-mono text-sm',
])

@php
    $displayedUuid = 'N/A';
    $fullUuid = 'No UUID available';
    
    if ($uuid) {
        $trimmedUuid = trim($uuid);
        if ($trimmedUuid) {
            $fullUuid = $trimmedUuid;
            if ($format === 'long') {
                $displayedUuid = $trimmedUuid;
            } else {
                // Short format: default to 8 characters like Git short hash
                $displayedUuid = substr($trimmedUuid, 0, $length);
            }
        }
    }
@endphp

<span class="{{ $className }}" title="{{ $fullUuid }}">
    {{ $displayedUuid }}
</span>
