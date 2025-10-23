# GlossarySpellingResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**glossary_id** | **string** | The glossary this spelling belongs to | [default to undefined]
**language_id** | **string** | The language of this spelling | [default to undefined]
**spelling** | **string** | The spelling variation | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**glossary** | [**GlossaryResource**](GlossaryResource.md) | Relationships (only included if loaded) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) |  | [optional] [default to undefined]
**item_translations** | [**Array&lt;ItemTranslationResource&gt;**](ItemTranslationResource.md) |  | [optional] [default to undefined]

## Example

```typescript
import { GlossarySpellingResource } from './api';

const instance: GlossarySpellingResource = {
    id,
    glossary_id,
    language_id,
    spelling,
    created_at,
    updated_at,
    glossary,
    language,
    item_translations,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
