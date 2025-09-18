@extends('layouts.app')

@section('content')
<x-layout.form-page entity="contexts" title="Edit Context" :action="route('contexts.update', $context)" method="PUT">
    @include('contexts._form')
</x-layout.form-page>
@endsection
