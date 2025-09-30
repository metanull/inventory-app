@extends('layouts.app')

@section('content')
<x-layout.show-page entity="collections" :title="$collection->internal_name" :model="$collection" :backward-compatibility="$collection->backward_compatibility">
    <x-display.description-list title="Information">
        <x-display.field label="Internal Name" :value="$collection->internal_name" variant="gray" />
        <x-display.field label="Type" :value="ucfirst($collection->type)" />
        <x-display.language-reference label="Language" :value="$collection->language" variant="gray" />
        <x-display.context-reference label="Context" :value="$collection->context" />
        <x-display.field label="Legacy ID" :value="$collection->backward_compatibility" variant="gray" />
    </x-display.description-list>
</x-layout.show-page>
@endsection
