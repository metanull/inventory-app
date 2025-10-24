@extends('layouts.app')

@section('content')
<x-layout.form-page 
    entity="partner_translations" 
    title="Edit Partner Translation" 
    :back-route="route('partner-translations.show', $partnerTranslation)"
    :submit-route="route('partner-translations.update', $partnerTranslation)"
    method="PUT">
    @include('partner-translations._form', ['partnerTranslation' => $partnerTranslation])
</x-layout.form-page>
@endsection
