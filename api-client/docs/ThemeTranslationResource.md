# ThemeTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**theme_id** | **string** | The theme this translation belongs to (ThemeResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context of this translation (ContextResource id) | [default to undefined]
**title** | **string** | The title of the theme translation | [default to undefined]
**description** | **string** | The description of the theme translation | [default to undefined]
**introduction** | **string** | The introduction of the theme translation | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**extra** | **object** | Extra data for translation (object, may be null) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ThemeTranslationResource } from './api';

const instance: ThemeTranslationResource = {
    id,
    theme_id,
    language_id,
    context_id,
    title,
    description,
    introduction,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
