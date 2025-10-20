{{-- Item Translations Section --}}
<div class="mt-8">
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Translations</h2>
                </div>
            <div>
                <a href="{{ route('item-translations.create', ['item_id' => $item->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                    Add Translation
                </a>
            </div>
        </div>

        <div class="p-6">
            @if($translationsByContext->isEmpty())
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No translations</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a translation for this item.</p>
                    <div class="mt-6">
                        <a href="{{ route('item-translations.create', ['item_id' => $item->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                            Add First Translation
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($translationsByContext as $contextId => $translations)
                        @php
                            $context = $translations->first()->context;
                            $isDefaultContext = $context && $context->is_default;
                        @endphp
                        
                        {{-- Context Group Header --}}
                        <div class="border-b border-gray-200 pb-2 mb-4">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                @if($isDefaultContext)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">
                                        Default
                                    </span>
                                @endif
                                <span>{{ $context ? $context->internal_name : 'No Context' }}</span>
                            </h4>
                        </div>

                        {{-- Translations in this context --}}
                        <div class="space-y-4 ml-4">
                            @foreach($translations as $translation)
                                <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                    <div class="p-4">
                <!-- Header with Language and Context -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-language class="h-5 w-5 text-blue-500" />
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $translation->language->internal_name ?? $translation->language_id }}
                            </span>
                            @if($translation->context)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $translation->context->internal_name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>                                <!-- Translation Name -->
                                <div class="mb-2">
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $translation->name }}
                                    </h4>
                                    @if($translation->alternate_name)
                                        <p class="text-sm text-gray-600">
                                            {{ $translation->alternate_name }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Description Preview -->
                                @if($translation->description)
                                    <div class="text-sm text-gray-700 mb-3 line-clamp-2">
                                        {{ Str::limit($translation->description, 200) }}
                                    </div>
                                @endif

                                <!-- Metadata -->
                                <div class="flex items-center text-xs text-gray-500 space-x-4 mb-3">
                                    @if($translation->type)
                                        <span>Type: {{ $translation->type }}</span>
                                    @endif
                                    @if($translation->dates)
                                        <span>Dates: {{ $translation->dates }}</span>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                                    <!-- View/Edit -->
                                    <a href="{{ route('item-translations.show', $translation) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                        <x-heroicon-o-eye class="h-4 w-4 mr-1" />
                                        View
                                    </a>
                                    <a href="{{ route('item-translations.edit', $translation) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-yellow-600 border border-transparent rounded text-xs text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition">
                                        <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                                        Edit
                                    </a>
                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('item-translations.destroy', $translation) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this translation?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded text-xs text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                                            <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
