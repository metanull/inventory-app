@props([
    'name' => 'author_id',
    'selected' => null,
    'required' => false,
    'placeholder' => 'Select or search for an author...',
])

@php
    $authors = \App\Models\Author::orderBy('name')->get(['id', 'name']);
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
