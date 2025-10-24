@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="partner_translations"
        :title="$partnerTranslation->name"
        :back-route="route('partner-translations.index')"
        :edit-route="route('partner-translations.edit', $partnerTranslation)"
        :delete-route="route('partner-translations.destroy', $partnerTranslation)"
        delete-confirm="Are you sure you want to delete this translation?"
        :backward-compatibility="$partnerTranslation->backward_compatibility"
    >
        @if(session('success'))
            <x-ui.alert :message="session('success')" type="success" entity="partner_translations" />
        @endif

        <x-display.description-list>
            <x-display.field label="Name" :value="$partnerTranslation->name" />
            <x-display.field label="Partner">
                @if($partnerTranslation->partner)
                    <a href="{{ route('partners.show', $partnerTranslation->partner) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $partnerTranslation->partner->internal_name }}
                    </a>
                @else
                    <span class="text-gray-400">N/A</span>
                @endif
            </x-display.field>
            <x-display.field label="Language">
                @if($partnerTranslation->language)
                    <a href="{{ route('languages.show', $partnerTranslation->language) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $partnerTranslation->language->internal_name }}
                    </a>
                @else
                    <span class="text-gray-400">{{ $partnerTranslation->language_id }}</span>
                @endif
            </x-display.field>
            <x-display.field label="Context">
                @if($partnerTranslation->context)
                    <a href="{{ route('contexts.show', $partnerTranslation->context) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $partnerTranslation->context->internal_name }}
                        @if($partnerTranslation->context->is_default)
                            <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">default</span>
                        @endif
                    </a>
                @else
                    <span class="text-gray-400">N/A</span>
                @endif
            </x-display.field>
            <x-display.field label="Description" :value="$partnerTranslation->description" full-width />
            <x-display.field label="City (Display)" :value="$partnerTranslation->city_display" />
            
            {{-- Address Section --}}
            <x-display.field label="Address Line 1" :value="$partnerTranslation->address_line_1" />
            <x-display.field label="Address Line 2" :value="$partnerTranslation->address_line_2" />
            <x-display.field label="Postal Code" :value="$partnerTranslation->postal_code" />
            <x-display.field label="Address Notes" :value="$partnerTranslation->address_notes" full-width />
            
            {{-- Contact Section --}}
            <x-display.field label="Contact Name" :value="$partnerTranslation->contact_name" />
            <x-display.field label="General Email" :value="$partnerTranslation->contact_email_general" />
            <x-display.field label="Press Email" :value="$partnerTranslation->contact_email_press" />
            <x-display.field label="Phone" :value="$partnerTranslation->contact_phone" />
            <x-display.field label="Website">
                @if($partnerTranslation->contact_website)
                    <a href="{{ $partnerTranslation->contact_website }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                        {{ $partnerTranslation->contact_website }}
                    </a>
                @endif
            </x-display.field>
            <x-display.field label="Contact Notes" :value="$partnerTranslation->contact_notes" full-width />
            
            @if($partnerTranslation->extra)
                <x-display.field label="Extra Data" full-width>
                    <pre class="text-xs bg-gray-50 p-2 rounded">{{ json_encode($partnerTranslation->extra, JSON_PRETTY_PRINT) }}</pre>
                </x-display.field>
            @endif
        </x-display.description-list>

        {{-- Images Section --}}
        @include('partner-translations._images')

        <!-- System Properties -->
        <x-system-properties 
            :id="$partnerTranslation->id"
            :backward-compatibility-id="$partnerTranslation->backward_compatibility"
            :created-at="$partnerTranslation->created_at"
            :updated-at="$partnerTranslation->updated_at"
        />
    </x-layout.show-page>
@endsection
