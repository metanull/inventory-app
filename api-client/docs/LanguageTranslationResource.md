# LanguageTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**display_language_id** | **string** |  | [default to undefined]
**name** | **string** |  | [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | Relationships | [optional] [default to undefined]
**display_language** | [**LanguageResource**](LanguageResource.md) |  | [optional] [default to undefined]
**backward_compatibility** | **string** | Metadata | [default to undefined]
**extra** | **object** |  | [default to undefined]
**created_at** | **string** | Timestamps | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { LanguageTranslationResource } from './api';

const instance: LanguageTranslationResource = {
    id,
    language_id,
    display_language_id,
    name,
    language,
    display_language,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
