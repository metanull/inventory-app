{{-- Welcome Feature Card Component --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 lg:p-8">
        <div class="flex items-center">
            {{ $icon }}
            <h2 class="ms-3 text-xl font-semibold text-gray-900">
                {{ $title }}
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            {{ $description }}
        </p>

        @isset($linkUrl)
            <p class="mt-4 text-sm">
                <a href="{{ $linkUrl }}" class="inline-flex items-center font-semibold text-blue-800 hover:text-blue-600 transition-colors duration-200">
                    {{ $linkText ?? 'Learn more' }}
                    <x-heroicon-o-arrow-right class="ms-1 size-5 fill-blue-600" />
                </a>
            </p>
        @endisset
    </div>
</div>
