@props([
    'name' => 'address_id',
    'selected' => null,
    'addresses' => null,
    'required' => false,
    'placeholder' => 'Select or search for an address...',
])

@php
    $addresses = $addresses ?? collect();
    $addressesForSelect = $addresses->map(function($address) {
        $label = $address->internal_name;
        if ($address->country) {
            $label .= ' (' . $address->country->internal_name . ')';
        }
        return [
            'id' => $address->id,
            'name' => $label,
        ];
    });
@endphp

<x-form.searchable-select 
    :name="$name"
    :options="$addressesForSelect"
    :selected="$selected"
    :placeholder="$placeholder"
    :required="$required"
    valueField="id"
    labelField="name"
/>
