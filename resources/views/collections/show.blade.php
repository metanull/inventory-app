@extends('layouts.app')

@section('content')
<x-layout.show-page entity="collections" :title="$collection->internal_name" :model="$collection">
    <x-display.description-list title="Information">
        <x-display.field label="Internal Name" :value="$collection->internal_name" variant="gray" />
        <x-display.reference-language label="Language" :value="$collection->language" />
        <x-display.reference-context label="Context" :value="$collection->context" variant="gray" />
        <x-display.field label="Legacy ID" :value="$collection->backward_compatibility" />
    </x-display.description-list>
</x-layout.show-page>
@endsection
