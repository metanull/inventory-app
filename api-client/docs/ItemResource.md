# ItemResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**type** | [**ItemType**](ItemType.md) | The type of the item: \&#39;object\&#39;, \&#39;monument\&#39;, \&#39;detail\&#39;, or \&#39;picture\&#39;. | [default to undefined]
**parent_id** | **string** | The parent item ID (for hierarchical relationships), nullable | [default to undefined]
**owner_reference** | **string** | Owner\&#39;s reference number for the item (external reference from owner) | [default to undefined]
**mwnf_reference** | **string** | MWNF reference number for the item (reference from MWNF system) | [default to undefined]
**parent** | [**ItemResource**](ItemResource.md) | The parent item (for hierarchical relationships), nullable (ItemResource) | [optional] [default to undefined]
**children** | [**Array&lt;ItemResource&gt;**](ItemResource.md) | The child items (for hierarchical relationships) (ItemResource[]) | [optional] [default to undefined]
**partner** | [**PartnerResource**](PartnerResource.md) | The partner owning the item (PartnerResource) | [optional] [default to undefined]
**project** | [**ProjectResource**](ProjectResource.md) | The project this item belongs to, nullable (ProjectResource) | [optional] [default to undefined]
**country** | [**CountryResource**](CountryResource.md) | The country this item is associated with, nullable (CountryResource) | [optional] [default to undefined]
**collection** | [**CollectionResource**](CollectionResource.md) | The collection that contains this item (CollectionResource) | [optional] [default to undefined]
**artists** | [**Array&lt;ArtistResource&gt;**](ArtistResource.md) | Artists associated with this item (ArtistResource[]) | [optional] [default to undefined]
**workshops** | [**Array&lt;WorkshopResource&gt;**](WorkshopResource.md) | Workshops associated with this item (WorkshopResource[]) | [optional] [default to undefined]
**tags** | [**Array&lt;TagResource&gt;**](TagResource.md) | Tags associated with this item (TagResource[]) | [optional] [default to undefined]
**translations** | [**Array&lt;ItemTranslationResource&gt;**](ItemTranslationResource.md) | Translations for this item (internationalization and contextualization) (ItemTranslationResource[]) | [optional] [default to undefined]
**itemImages** | [**Array&lt;ItemImageResource&gt;**](ItemImageResource.md) | Item images attached to this item with display ordering (ItemImageResource[]) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ItemResource } from './api';

const instance: ItemResource = {
    id,
    internal_name,
    backward_compatibility,
    type,
    parent_id,
    owner_reference,
    mwnf_reference,
    parent,
    children,
    partner,
    project,
    country,
    collection,
    artists,
    workshops,
    tags,
    translations,
    itemImages,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
