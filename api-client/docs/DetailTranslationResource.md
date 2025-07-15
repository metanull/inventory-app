# DetailTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**detail_id** | **string** | The detail this translation belongs to (DetailResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context of this translation (ContextResource id) | [default to undefined]
**name** | **string** | The name of the detail translation | [default to undefined]
**alternate_name** | **string** | The alternate name of the detail translation | [default to undefined]
**description** | **string** | The description of the detail translation | [default to undefined]
**author_id** | **string** | The author of the translation (AuthorResource id) | [default to undefined]
**text_copy_editor_id** | **string** | The text copy editor of the translation (UserResource id) | [default to undefined]
**translator_id** | **string** | The translator of the translation (UserResource id) | [default to undefined]
**translation_copy_editor_id** | **string** | The translation copy editor of the translation (UserResource id) | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**extra** | **object** | Extra data for translation (object, may be null) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**detail** | [**DetailResource**](DetailResource.md) | The detail relationship (DetailResource) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The language relationship (LanguageResource) | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The context relationship (ContextResource) | [optional] [default to undefined]
**author** | [**AuthorResource**](AuthorResource.md) | The author relationship (AuthorResource) | [optional] [default to undefined]
**text_copy_editor** | [**AuthorResource**](AuthorResource.md) | The text copy editor relationship (AuthorResource) | [optional] [default to undefined]
**translator** | [**AuthorResource**](AuthorResource.md) | The translator relationship (AuthorResource) | [optional] [default to undefined]
**translation_copy_editor** | [**AuthorResource**](AuthorResource.md) | The translation copy editor relationship (AuthorResource) | [optional] [default to undefined]

## Example

```typescript
import { DetailTranslationResource } from './api';

const instance: DetailTranslationResource = {
    id,
    detail_id,
    language_id,
    context_id,
    name,
    alternate_name,
    description,
    author_id,
    text_copy_editor_id,
    translator_id,
    translation_copy_editor_id,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
    detail,
    language,
    context,
    author,
    text_copy_editor,
    translator,
    translation_copy_editor,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
