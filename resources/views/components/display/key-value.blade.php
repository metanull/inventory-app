@props([
    'data' => [],
    'emptyMessage' => 'No additional metadata',
])

@if(empty($data))
    <p class="text-gray-500 text-sm italic">{{ $emptyMessage }}</p>
@else
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach($data as $key => $value)
            <div class="sm:col-span-1">
                <dt class="text-sm font-semibold text-gray-700 break-words">
                    {{ Str::title(str_replace('_', ' ', $key)) }}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 break-words">
                    @if(is_array($value))
                        @if(empty($value))
                            <span class="text-gray-500 italic">empty</span>
                        @else
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($value as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif(is_null($value))
                        <span class="text-gray-500 italic">null</span>
                    @elseif($value === '')
                        <span class="text-gray-500 italic">empty</span>
                    @else
                        {{ $value }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
@endif
