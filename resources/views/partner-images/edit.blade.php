<x-image-attachment.edit-page 
    :entity="$partner"
    :entity-image="$partnerImage"
    entity-name="partner"
    :update-route="route('partners.partner-images.update', [$partner, $partnerImage])"
    :back-route="route('partners.show', $partner)"
    :view-route="route('partners.partner-images.view', [$partner, $partnerImage])"
/>
