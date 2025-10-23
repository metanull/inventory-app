@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="glossary"
        title="Glossary Entry Detail"
        :back-route="route('glossaries.index')"
        :edit-route="route('glossaries.edit', $glossary)"
        :delete-route="route('glossaries.destroy', $glossary)"
        delete-confirm="Are you sure you want to delete this glossary entry?"
        :backward-compatibility="$glossary->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="glossary" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$glossary->internal_name" />
        </x-display.description-list>

        <!-- Translations Section -->
        @include('glossary._translations')

        <!-- Spellings Section -->
        @include('glossary._spellings')

        <!-- Synonyms Section -->
        @if($glossary->synonyms->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Synonyms</h3>
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @foreach($glossary->synonyms as $synonym)
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

        <!-- System Properties -->
        <x-system-properties 
            :id="$glossary->id"
            :backward-compatibility-id="$glossary->backward_compatibility"
            :created-at="$glossary->created_at"
            :updated-at="$glossary->updated_at"
        />
    </x-layout.show-page>
@endsection
