<x-image-attachment.page 
    :entity="$partner"
    entity-name="partner"
    :available-images="$availableImages"
    :store-route="route('partners.partner-images.store', $partner)"
    :back-route="route('partners.show', $partner)"
/>
