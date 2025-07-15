# ExhibitionTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**exhibition_id** | **string** | The exhibition this translation belongs to (ExhibitionResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context of this translation (ContextResource id) | [default to undefined]
**title** | **string** | The title of the exhibition translation | [default to undefined]
**description** | **string** | The description of the exhibition translation | [default to undefined]
**url** | **string** | The URL for the exhibition translation | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**extra** | **object** | Extra data for translation (object, may be null) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ExhibitionTranslationResource } from './api';

const instance: ExhibitionTranslationResource = {
    id,
    exhibition_id,
    language_id,
    context_id,
    title,
    description,
    url,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
