@csrf

<div class="p-6 space-y-6">
    {{-- Required Fields --}}
    <x-form.section heading="Basic Information">
        <x-form.field label="Partner" name="partner_id" variant="gray" required>
                <x-form.entity-select 
                    name="partner_id" 
                    :value="old('partner_id', $partnerTranslation->partner_id ?? $selectedPartnerId ?? null)"
                    :options="$partners"
                    displayField="internal_name"
                    placeholder="Select a partner..."
                    searchPlaceholder="Type to search partners..."
                    required
                    entity="partners"
                />
            </x-form.field>

            <x-form.field label="Language" name="language_id" variant="gray" required>
                <x-form.entity-select 
                    name="language_id" 
                    :value="old('language_id', $partnerTranslation->language_id ?? ($defaultLanguage->id ?? null))"
                    :options="$languages"
                    displayField="internal_name"
                    placeholder="Select a language..."
                    searchPlaceholder="Type to search languages..."
                    required
                    :showId="true"
                />
            </x-form.field>

            <x-form.field label="Context" name="context_id" variant="gray" required>
                <x-form.entity-select 
                    name="context_id" 
                    :value="old('context_id', $partnerTranslation->context_id ?? ($defaultContext->id ?? null))"
                    :options="$contexts"
                    displayField="internal_name"
                    placeholder="Select a context..."
                    searchPlaceholder="Type to search contexts..."
                    required
                />
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
                <x-form.textarea 
                    name="description" 
                    :value="old('description', $partnerTranslation->description ?? '')"
                    rows="4"
                    placeholder="Partner description"
                />
            </x-form.field>

            <x-form.field label="City (Display)" name="city_display" variant="gray">
                <x-form.input 
                    name="city_display" 
                    :value="old('city_display', $partnerTranslation->city_display ?? '')" 
                    placeholder="City name to display (may differ from actual address)"
                />
            </x-form.field>
    </x-form.section>

    {{-- Address Information --}}
    <x-form.section heading="Address">
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
                <x-form.textarea 
                    name="address_notes" 
                    :value="old('address_notes', $partnerTranslation->address_notes ?? '')"
                    rows="2"
                    placeholder="Additional address information or directions"
                />
            </x-form.field>
    </x-form.section>

    {{-- Contact Information --}}
    <x-form.section heading="Contact Information">
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
                <x-form.textarea 
                    name="contact_notes" 
                    :value="old('contact_notes', $partnerTranslation->contact_notes ?? '')"
                    rows="2"
                    placeholder="Additional contact information"
                />
            </x-form.field>
    </x-form.section>

    {{-- Advanced --}}
    <x-form.section heading="Advanced" :border="false">
        <x-form.field label="Backward Compatibility ID" name="backward_compatibility" variant="gray">
                <x-form.input 
                    name="backward_compatibility" 
                    :value="old('backward_compatibility', $partnerTranslation->backward_compatibility ?? '')" 
                    placeholder="Legacy system reference"
                />
            </x-form.field>
    </x-form.section>
</div>

<x-form.actions 
    entity="partner_translations"
    :cancel-route="isset($partnerTranslation) && $partnerTranslation->exists ? route('partner-translations.show', $partnerTranslation) : route('partner-translations.index')"
/>
