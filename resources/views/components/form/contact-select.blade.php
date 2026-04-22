@props([
    'name' => 'contact_id',
    'selected' => null,
    'contacts' => null,
    'required' => false,
    'placeholder' => 'Select or search for a contact...',
])

@php
    $contacts = $contacts ?? collect();
    $contactsForSelect = $contacts->map(function($contact) {
        return [
            'id' => $contact->id,
            'name' => $contact->internal_name,
        ];
    });
@endphp

<x-form.searchable-select 
    :name="$name"
    :options="$contactsForSelect"
    :selected="$selected"
    :placeholder="$placeholder"
    :required="$required"
    valueField="id"
    labelField="name"
/>
