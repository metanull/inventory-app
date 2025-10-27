@props([
    'entity',
    'title' => null,
])

@php
    $entitySingular = \Illuminate\Support\Str::singular($entity);
    $title = $title ?? 'Create ' . \Illuminate\Support\Str::title($entitySingular);
    $backRoute = route($entity . '.index');
    $submitRoute = route($entity . '.store');
@endphp

@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        :entity="$entity"
        :title="$title"
        :back-route="$backRoute"
        :submit-route="$submitRoute"
    >
        {{ $slot }}
    </x-layout.form-page>
@endsection
