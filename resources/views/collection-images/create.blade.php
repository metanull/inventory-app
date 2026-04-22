<x-image-attachment.page 
    :entity="$collection"
    entity-name="collection"
    :available-images="$availableImages"
    :store-route="route('collections.collection-images.store', $collection)"
    :back-route="route('collections.show', $collection)"
/>
