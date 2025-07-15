# GalleryResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**translations** | [**Array&lt;GalleryTranslationResource&gt;**](GalleryTranslationResource.md) | Translations for this gallery (GalleryTranslationResource[]) | [optional] [default to undefined]
**partners** | [**Array&lt;PartnerResource&gt;**](PartnerResource.md) | Partners associated with this gallery (PartnerResource[]) | [optional] [default to undefined]
**items** | [**Array&lt;ItemResource&gt;**](ItemResource.md) | Items associated with this gallery (ItemResource[]) | [optional] [default to undefined]
**details** | [**Array&lt;DetailResource&gt;**](DetailResource.md) | Details associated with this gallery (DetailResource[]) | [optional] [default to undefined]
**items_count** | **string** | The number of items in this gallery (computed) | [optional] [default to undefined]
**details_count** | **string** | The number of details in this gallery (computed) | [optional] [default to undefined]
**total_content_count** | **string** | The total number of content items in this gallery (computed) | [optional] [default to undefined]
**partners_count** | **string** | The total number of partners in this gallery (computed) | [optional] [default to undefined]
**translations_count** | **string** | The total number of translations in this gallery (computed) | [optional] [default to undefined]

## Example

```typescript
import { GalleryResource } from './api';

const instance: GalleryResource = {
    id,
    internal_name,
    backward_compatibility,
    created_at,
    updated_at,
    translations,
    partners,
    items,
    details,
    items_count,
    details_count,
    total_content_count,
    partners_count,
    translations_count,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
