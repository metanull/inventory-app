# GalleryTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**gallery_id** | **string** | The gallery this translation belongs to (GalleryResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context of this translation (ContextResource id) | [default to undefined]
**title** | **string** | The title of the gallery translation | [default to undefined]
**description** | **string** | The description of the gallery translation | [default to undefined]
**url** | **string** | The URL for the gallery translation | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**extra** | **object** | Extra data for translation (object, may be null) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**gallery** | [**GalleryResource**](GalleryResource.md) | The gallery relationship (GalleryResource) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The language relationship (LanguageResource) | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The context relationship (ContextResource) | [optional] [default to undefined]

## Example

```typescript
import { GalleryTranslationResource } from './api';

const instance: GalleryTranslationResource = {
    id,
    gallery_id,
    language_id,
    context_id,
    title,
    description,
    url,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
    gallery,
    language,
    context,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
