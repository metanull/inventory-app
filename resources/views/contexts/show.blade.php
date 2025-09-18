@extends('layouts.app')

@section('content')
<x-layout.show-page entity="contexts" :title="$context->internal_name" :model="$context" :backward-compatibility="$context->backward_compatibility">
    <x-display.description-list title="Information">
        <x-display.field label="Internal Name" :value="$context->internal_name" variant="gray" />
        <x-display.boolean label="Default" :value="$context->is_default" />
        <x-display.field label="Legacy ID" :value="$context->backward_compatibility" variant="gray" />
    </x-display.description-list>
</x-layout.show-page>
@endsection
