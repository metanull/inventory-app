@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="partner_translations" 
    title="Create Partner Translation" 
    :back-route="route('partner-translations.index')"
    :submit-route="route('partner-translations.store')">
    @include('partner-translations._form', ['partnerTranslation' => null])
</x-layout.form-page>
@endsection
