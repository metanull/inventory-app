@extends('layouts.app')

@section('content')
    @php
        $c = $entityColor('partner_translations');
    @endphp
    
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
                <x-display.partner-reference :partner="$partnerTranslation->partner" />
            </x-display.field>
            <x-display.field label="Language">
                <x-display.language-reference :language="$partnerTranslation->language" />
            </x-display.field>
            <x-display.field label="Context">
                <x-display.context-reference :context="$partnerTranslation->context" />
            </x-display.field>
            <x-display.field label="Description" full-width>
                <x-display.markdown :content="$partnerTranslation->description" />
            </x-display.field>
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
                    <a href="{{ $partnerTranslation->contact_website }}" target="_blank" class="{{ $c['accentLink'] }}">
                        {{ $partnerTranslation->contact_website }}
                    </a>
                @endif
            </x-display.field>
            <x-display.field label="Contact Notes" :value="$partnerTranslation->contact_notes" full-width />
            
            @if($partnerTranslation->extra)
                <x-display.field label="Metadata" full-width>
                    <x-display.key-value :data="$partnerTranslation->extra_decoded" />
                </x-display.field>
            @endif
        </x-display.description-list>

        {{-- Images Section --}}
        <x-entity.translation-images-section :model="$partnerTranslation" entity="partner-translations" />

        <!-- System Properties -->
        <x-system-properties 
            :id="$partnerTranslation->id"
            :backward-compatibility-id="$partnerTranslation->backward_compatibility"
            :created-at="$partnerTranslation->created_at"
            :updated-at="$partnerTranslation->updated_at"
        />
    </x-layout.show-page>
@endsection
