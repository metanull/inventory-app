@props([
    'name' => 'author_id',
    'selected' => null,
    'authors' => null,
    'required' => false,
    'placeholder' => 'Select or search for an author...',
])

@php
    $authors = $authors ?? collect();
@endphp

<x-form.searchable-select 
    :name="$name"
    :options="$authors"
    :selected="$selected"
    :placeholder="$placeholder"
    :required="$required"
    valueField="id"
    labelField="name"
/>
