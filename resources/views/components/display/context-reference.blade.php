@props(['context' => null, 'link' => true])

@php($c = $entityColor('contexts'))

@if($context)
    @if($link)
        <a 
            href="{{ route('contexts.show', $context) }}" 
            class="{{ $c['accentLink'] }}"
        >
            {{ $context->internal_name }}
            @if($context->is_default)
                <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">default</span>
            @endif
        </a>
    @else
        {{ $context->internal_name }}
        @if($context->is_default)
            <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">default</span>
        @endif
    @endif
@else
    <span class="text-gray-400">N/A</span>
@endif