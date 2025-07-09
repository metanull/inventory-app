# CollectionResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**context_id** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | Relationships | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) |  | [optional] [default to undefined]
**translations** | [**Array&lt;CollectionTranslationResource&gt;**](CollectionTranslationResource.md) |  | [optional] [default to undefined]
**partners** | [**Array&lt;PartnerResource&gt;**](PartnerResource.md) |  | [optional] [default to undefined]
**items** | [**Array&lt;ItemResource&gt;**](ItemResource.md) |  | [optional] [default to undefined]
**items_count** | **string** | Computed attributes | [optional] [default to undefined]
**partners_count** | **string** |  | [optional] [default to undefined]
**translations_count** | **string** |  | [optional] [default to undefined]

## Example

```typescript
import { CollectionResource } from './api';

const instance: CollectionResource = {
    id,
    internal_name,
    language_id,
    context_id,
    backward_compatibility,
    created_at,
    updated_at,
    language,
    context,
    translations,
    partners,
    items,
    items_count,
    partners_count,
    translations_count,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
