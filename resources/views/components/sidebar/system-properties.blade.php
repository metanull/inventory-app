{{--
    System Properties Sidebar Card
    Displays ID, timestamps, and backward compatibility info in compact format
--}}

@props([
    'id' => null,
    'backwardCompatibilityId' => null,
    'createdAt' => null,
    'updatedAt' => null,
])

<x-sidebar.card title="System Info" icon="information-circle" compact>
    <dl class="space-y-2 text-xs">
        @if($id)
            <div>
                <dt class="text-gray-500 font-medium">ID</dt>
                <dd class="font-mono text-gray-900 break-all">
                    {{ $id }}
                </dd>
            </div>
        @endif

        @if($backwardCompatibilityId)
            <div>
                <dt class="text-gray-500 font-medium">Legacy ID</dt>
                <dd class="font-mono text-gray-900">{{ $backwardCompatibilityId }}</dd>
            </div>
        @endif

        @if($createdAt)
            <div>
                <dt class="text-gray-500 font-medium">Created</dt>
                <dd class="text-gray-900">
                    <x-format.date :date="$createdAt" format="short" />
                </dd>
            </div>
        @endif

        @if($updatedAt)
            <div>
                <dt class="text-gray-500 font-medium">Updated</dt>
                <dd class="text-gray-900">
                    <x-format.date :date="$updatedAt" format="short" />
                </dd>
            </div>
        @endif
    </dl>
</x-sidebar.card>
