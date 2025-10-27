<x-image-attachment.edit-page 
    :entity="$item"
    :entity-image="$itemImage"
    entity-name="item"
    :update-route="route('items.item-images.update', [$item, $itemImage])"
    :back-route="route('items.show', $item)"
    :view-route="route('items.item-images.view', [$item, $itemImage])"
/>
