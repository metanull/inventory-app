<x-image-attachment.edit-page 
    :entity="$collection"
    :entity-image="$collectionImage"
    entity-name="collection"
    :update-route="route('collections.collection-images.update', [$collection, $collectionImage])"
    :back-route="route('collections.show', $collection)"
    :view-route="route('collections.collection-images.view', [$collection, $collectionImage])"
/>
