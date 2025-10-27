<x-image-attachment.edit-page 
    :entity="$partnerTranslation"
    :entity-image="$partnerTranslationImage"
    entity-name="translation"
    :update-route="route('partner-translations.partner-translation-images.update', [$partnerTranslation, $partnerTranslationImage])"
    :back-route="route('partner-translations.show', $partnerTranslation)"
    :view-route="route('partner-translations.partner-translation-images.view', [$partnerTranslation, $partnerTranslationImage])"
/>
