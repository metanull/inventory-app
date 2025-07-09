# CollectionTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**collection_id** | **string** |  | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**context_id** | **string** |  | [default to undefined]
**title** | **string** |  | [default to undefined]
**description** | **string** |  | [default to undefined]
**url** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [default to undefined]
**extra** | **Array&lt;any&gt;** |  | [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]
**collection** | [**CollectionResource**](CollectionResource.md) | Relationships | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) |  | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) |  | [optional] [default to undefined]

## Example

```typescript
import { CollectionTranslationResource } from './api';

const instance: CollectionTranslationResource = {
    id,
    collection_id,
    language_id,
    context_id,
    title,
    description,
    url,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
    collection,
    language,
    context,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
