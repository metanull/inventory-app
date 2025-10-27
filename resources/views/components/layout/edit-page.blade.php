@props([
    'entity',
    'model',
    'title' => null,
])

@php
    $entitySingular = \Illuminate\Support\Str::singular($entity);
    $title = $title ?? 'Edit ' . \Illuminate\Support\Str::title($entitySingular);
    $backRoute = route($entity . '.show', $model);
    $submitRoute = route($entity . '.update', $model);
@endphp

@extends('layouts.app')

@section('content')
    <x-layout.form-page 
        :entity="$entity"
        :title="$title"
        :back-route="$backRoute"
        :submit-route="$submitRoute"
        method="PUT"
    >
        {{ $slot }}
    </x-layout.form-page>
@endsection
