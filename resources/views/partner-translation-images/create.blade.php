<x-image-attachment.page 
    :entity="$partnerTranslation"
    entity-name="translation"
    :available-images="$availableImages"
    :store-route="route('partner-translations.partner-translation-images.store', $partnerTranslation)"
    :back-route="route('partner-translations.show', $partnerTranslation)"
/>
