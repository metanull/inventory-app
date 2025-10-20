{{--
    System Properties Component
    Displays ID, backward compatibility ID, created_at, and updated_at in a consistent format
    Similar to Vue's SystemProperties.vue component
    
    Usage:
    <x-system-properties 
        :id="$model->id"
        :backward-compatibility-id="$model->backward_compatibility_id ?? null"
        :created-at="$model->created_at"
        :updated-at="$model->updated_at"
    />
--}}

@props([
    'id' => null,
    'backwardCompatibilityId' => null,
    'createdAt' => null,
    'updatedAt' => null,
])

<div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">System Properties</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Internal data managed by the System.</p>
    </div>
    <div class="border-t border-gray-200">
        <dl>
            {{-- ID Row --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">ID</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <x-format.uuid :uuid="$id" format="long" />
                </dd>
            </div>

            {{-- Backward Compatibility ID Row (if present) --}}
            @if($backwardCompatibilityId)
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Backward Compatibility ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="font-mono text-sm">{{ $backwardCompatibilityId }}</span>
                    </dd>
                </div>
            @endif

            {{-- Created At Row --}}
            <div class="{{ $backwardCompatibilityId ? 'bg-gray-50' : 'bg-white' }} px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <x-format.date :date="$createdAt" format="medium" show-time />
                </dd>
            </div>

            {{-- Updated At Row --}}
            <div class="{{ $backwardCompatibilityId ? 'bg-white' : 'bg-gray-50' }} px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <x-format.date :date="$updatedAt" format="medium" show-time />
                </dd>
            </div>
        </dl>
    </div>
</div>
