# GalleryResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]
**translations** | [**Array&lt;GalleryTranslationResource&gt;**](GalleryTranslationResource.md) | Relationships | [optional] [default to undefined]
**partners** | [**Array&lt;PartnerResource&gt;**](PartnerResource.md) |  | [optional] [default to undefined]
**items** | [**Array&lt;ItemResource&gt;**](ItemResource.md) |  | [optional] [default to undefined]
**details** | [**Array&lt;DetailResource&gt;**](DetailResource.md) |  | [optional] [default to undefined]
**items_count** | **string** | Computed attributes | [optional] [default to undefined]
**details_count** | **string** |  | [optional] [default to undefined]
**total_content_count** | **string** |  | [optional] [default to undefined]
**partners_count** | **string** |  | [optional] [default to undefined]
**translations_count** | **string** |  | [optional] [default to undefined]

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
