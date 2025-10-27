@props(['model'])

{{-- Glossary Synonyms Section --}}
@if($model->synonyms->isNotEmpty())
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Synonyms</h3>
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($model->synonyms as $synonym)
                    <li class="px-6 py-4">
                        <a href="{{ route('glossaries.show', $synonym) }}" class="block hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-blue-600">{{ $synonym->internal_name }}</p>
                                <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400" />
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
