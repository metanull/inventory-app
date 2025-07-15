# PictureTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**picture_id** | **string** | The picture this translation belongs to (PictureResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context of this translation (ContextResource id) | [default to undefined]
**description** | **string** | The description of the picture translation | [default to undefined]
**caption** | **string** | The caption of the picture translation | [default to undefined]
**author_id** | **string** | The author of the translation (AuthorResource id) | [default to undefined]
**text_copy_editor_id** | **string** | The text copy editor of the translation (UserResource id) | [default to undefined]
**translator_id** | **string** | The translator of the translation (UserResource id) | [default to undefined]
**translation_copy_editor_id** | **string** | The translation copy editor of the translation (UserResource id) | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**extra** | **object** | Extra data for translation (object, may be null) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { PictureTranslationResource } from './api';

const instance: PictureTranslationResource = {
    id,
    picture_id,
    language_id,
    context_id,
    description,
    caption,
    author_id,
    text_copy_editor_id,
    translator_id,
    translation_copy_editor_id,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
