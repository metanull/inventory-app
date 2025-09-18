@props([
    'title' => 'Information',
])

<div class="bg-white shadow sm:rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">{{ $title }}</h2>
    </div>
    <div class="px-4 py-6 sm:px-6 space-y-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{ $slot }}
        </dl>
    </div>
</div>