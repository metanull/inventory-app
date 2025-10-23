# GlossaryTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**glossary_id** | **string** | The glossary this translation belongs to | [default to undefined]
**language_id** | **string** | The language of this translation | [default to undefined]
**definition** | **string** | The definition/translation text | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**glossary** | [**GlossaryResource**](GlossaryResource.md) | Relationships (only included if loaded) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) |  | [optional] [default to undefined]

## Example

```typescript
import { GlossaryTranslationResource } from './api';

const instance: GlossaryTranslationResource = {
    id,
    glossary_id,
    language_id,
    definition,
    created_at,
    updated_at,
    glossary,
    language,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
