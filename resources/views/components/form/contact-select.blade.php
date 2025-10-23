@props([
    'name' => 'contact_id',
    'selected' => null,
    'required' => false,
    'placeholder' => 'Select or search for a contact...',
])

@php
    $contacts = \App\Models\Contact::orderBy('internal_name')->get(['id', 'internal_name']);
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
