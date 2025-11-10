{{--
    Sidebar Card for System Properties
    Compact display for two-column layout
--}}

@props([
    'id' => null,
    'backwardCompatibilityId' => null,
    'createdAt' => null,
    'updatedAt' => null,
])

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">System Info</h3>
    
    <div class="space-y-2 text-xs">
        <!-- ID -->
        <div>
            <dt class="text-gray-500 font-medium">ID</dt>
            <dd class="text-gray-900 font-mono text-xs mt-0.5">
                <x-format.uuid :uuid="$id" format="long" />
            </dd>
        </div>

        <!-- Backward Compatibility ID -->
        @if($backwardCompatibilityId)
            <div class="pt-2 border-t border-gray-100">
                <dt class="text-gray-500 font-medium">Legacy ID</dt>
                <dd class="text-gray-900 font-mono text-xs mt-0.5">{{ $backwardCompatibilityId }}</dd>
            </div>
        @endif

        <!-- Created At -->
        <div class="pt-2 border-t border-gray-100">
            <dt class="text-gray-500 font-medium">Created</dt>
            <dd class="text-gray-900 mt-0.5">
                <x-format.date :date="$createdAt" format="short" />
            </dd>
        </div>

        <!-- Updated At -->
        <div class="pt-2 border-t border-gray-100">
            <dt class="text-gray-500 font-medium">Updated</dt>
            <dd class="text-gray-900 mt-0.5">
                <x-format.date :date="$updatedAt" format="short" />
            </dd>
        </div>
    </div>
</div>
