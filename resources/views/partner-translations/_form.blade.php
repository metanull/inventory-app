@csrf

<div class="p-6 space-y-6">
    {{-- Required Fields --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Partner" name="partner_id" variant="gray" required>
                <select name="partner_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a partner...</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" @selected(old('partner_id', $partnerTranslation->partner_id ?? $selectedPartnerId ?? '') === $partner->id)>
                            {{ $partner->internal_name }}
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="Language" name="language_id" variant="gray" required>
                <select name="language_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a language...</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}" @selected(old('language_id', $partnerTranslation->language_id ?? ($defaultLanguage->id ?? '')) === $language->id)>
                            {{ $language->internal_name }}
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="Context" name="context_id" variant="gray" required>
                <select name="context_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a context...</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}" @selected(old('context_id', $partnerTranslation->context_id ?? ($defaultContext->id ?? '')) === $context->id)>
                            {{ $context->internal_name }}
                            @if($context->is_default) (default) @endif
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="Name" name="name" variant="gray" required>
                <x-form.input 
                    name="name" 
                    :value="old('name', $partnerTranslation->name ?? '')" 
                    required 
                    placeholder="Partner name in this language"
                />
            </x-form.field>

            <x-form.field label="Description" name="description" variant="gray">
                <textarea 
                    name="description" 
                    rows="4"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Partner description"
                >{{ old('description', $partnerTranslation->description ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="City (Display)" name="city_display" variant="gray">
                <x-form.input 
                    name="city_display" 
                    :value="old('city_display', $partnerTranslation->city_display ?? '')" 
                    placeholder="City name to display (may differ from actual address)"
                />
            </x-form.field>
        </div>
    </div>

    {{-- Address Information --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Address</h3>
        
        <div class="space-y-6">
            <x-form.field label="Address Line 1" name="address_line_1" variant="gray">
                <x-form.input 
                    name="address_line_1" 
                    :value="old('address_line_1', $partnerTranslation->address_line_1 ?? '')" 
                    placeholder="Street address"
                />
            </x-form.field>

            <x-form.field label="Address Line 2" name="address_line_2" variant="gray">
                <x-form.input 
                    name="address_line_2" 
                    :value="old('address_line_2', $partnerTranslation->address_line_2 ?? '')" 
                    placeholder="Apartment, suite, etc."
                />
            </x-form.field>

            <x-form.field label="Postal Code" name="postal_code" variant="gray">
                <x-form.input 
                    name="postal_code" 
                    :value="old('postal_code', $partnerTranslation->postal_code ?? '')" 
                    placeholder="ZIP / Postal code"
                />
            </x-form.field>

            <x-form.field label="Address Notes" name="address_notes" variant="gray">
                <textarea 
                    name="address_notes" 
                    rows="2"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Additional address information or directions"
                >{{ old('address_notes', $partnerTranslation->address_notes ?? '') }}</textarea>
            </x-form.field>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Contact Name" name="contact_name" variant="gray">
                <x-form.input 
                    name="contact_name" 
                    :value="old('contact_name', $partnerTranslation->contact_name ?? '')" 
                    placeholder="Primary contact person"
                />
            </x-form.field>

            <x-form.field label="General Email" name="contact_email_general" variant="gray">
                <x-form.input 
                    type="email"
                    name="contact_email_general" 
                    :value="old('contact_email_general', $partnerTranslation->contact_email_general ?? '')" 
                    placeholder="general@example.com"
                />
            </x-form.field>

            <x-form.field label="Press Email" name="contact_email_press" variant="gray">
                <x-form.input 
                    type="email"
                    name="contact_email_press" 
                    :value="old('contact_email_press', $partnerTranslation->contact_email_press ?? '')" 
                    placeholder="press@example.com"
                />
            </x-form.field>

            <x-form.field label="Phone" name="contact_phone" variant="gray">
                <x-form.input 
                    name="contact_phone" 
                    :value="old('contact_phone', $partnerTranslation->contact_phone ?? '')" 
                    placeholder="+1 234 567 8900"
                />
            </x-form.field>

            <x-form.field label="Website" name="contact_website" variant="gray">
                <x-form.input 
                    type="url"
                    name="contact_website" 
                    :value="old('contact_website', $partnerTranslation->contact_website ?? '')" 
                    placeholder="https://example.com"
                />
            </x-form.field>

            <x-form.field label="Contact Notes" name="contact_notes" variant="gray">
                <textarea 
                    name="contact_notes" 
                    rows="2"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Additional contact information"
                >{{ old('contact_notes', $partnerTranslation->contact_notes ?? '') }}</textarea>
            </x-form.field>
        </div>
    </div>

    {{-- Advanced --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Advanced</h3>
        
        <div class="space-y-6">
            <x-form.field label="Backward Compatibility ID" name="backward_compatibility" variant="gray">
                <x-form.input 
                    name="backward_compatibility" 
                    :value="old('backward_compatibility', $partnerTranslation->backward_compatibility ?? '')" 
                    placeholder="Legacy system reference"
                />
            </x-form.field>
        </div>
    </div>
</div>

<x-form.actions 
    :cancel-route="isset($partnerTranslation) && $partnerTranslation->exists ? route('partner-translations.show', $partnerTranslation) : route('partner-translations.index')"
/>
