<x-image-attachment.page 
    :entity="$item"
    entity-name="item"
    :available-images="$availableImages"
    :store-route="route('items.item-images.store', $item)"
    :back-route="route('items.show', $item)"
/>
