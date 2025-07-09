# ItemResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the item (GUID) | [default to undefined]
**internal_name** | **string** | The name of the item, it shall only be used internally | [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this item corresponds to a legacy item from the MWNF3 database, nullable | [default to undefined]
**type** | **string** | The type of the item, either \&#39;object\&#39; or \&#39;monument\&#39; | [default to undefined]
**owner_reference** | **string** | Owner\&#39;s reference number for the item | [default to undefined]
**mwnf_reference** | **string** | MWNF reference number for the item | [default to undefined]
**partner** | [**PartnerResource**](PartnerResource.md) | The partner owning the item | [optional] [default to undefined]
**project** | [**ProjectResource**](ProjectResource.md) | The project this item belongs to, nullable | [optional] [default to undefined]
**country** | [**CountryResource**](CountryResource.md) | The country this item is associated with, nullable | [optional] [default to undefined]
**artists** | [**Array&lt;ArtistResource&gt;**](ArtistResource.md) | Artists associated with this item | [default to undefined]
**workshops** | [**Array&lt;WorkshopResource&gt;**](WorkshopResource.md) | Workshops associated with this item | [default to undefined]
**tags** | [**Array&lt;TagResource&gt;**](TagResource.md) | Tags associated with this item | [optional] [default to undefined]
**translations** | [**Array&lt;ItemTranslationResource&gt;**](ItemTranslationResource.md) | Translations for this item (internationalization and contextualization) | [optional] [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { ItemResource } from './api';

const instance: ItemResource = {
    id,
    internal_name,
    backward_compatibility,
    type,
    owner_reference,
    mwnf_reference,
    partner,
    project,
    country,
    artists,
    workshops,
    tags,
    translations,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
