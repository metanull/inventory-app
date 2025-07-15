# ItemTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**item_id** | **string** | The item this translation belongs to (ItemResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context of this translation (ContextResource id) | [default to undefined]
**name** | **string** | The name of the item translation | [default to undefined]
**alternate_name** | **string** | The alternate name of the item translation | [default to undefined]
**description** | **string** | The description of the item translation | [default to undefined]
**type** | **string** | The type of the item translation | [default to undefined]
**holder** | **string** | The holder of the item | [default to undefined]
**owner** | **string** | The owner of the item | [default to undefined]
**initial_owner** | **string** | The initial owner of the item | [default to undefined]
**dates** | **string** | The dates associated with the item | [default to undefined]
**location** | **string** | The location associated with the item | [default to undefined]
**dimensions** | **string** | The dimensions of the item | [default to undefined]
**place_of_production** | **string** | The place of production of the item | [default to undefined]
**method_for_datation** | **string** | The method for datation of the item | [default to undefined]
**method_for_provenance** | **string** | The method for provenance of the item | [default to undefined]
**obtention** | **string** | The obtention of the item | [default to undefined]
**bibliography** | **string** | The bibliography for the item | [default to undefined]
**author_id** | **string** | The author of the item (AuthorResource id) | [default to undefined]
**text_copy_editor_id** | **string** | The copy-editor of the item (AuthorResource id) | [default to undefined]
**translator_id** | **string** | The translator of the item translation (AuthorResource id) | [default to undefined]
**translation_copy_editor_id** | **string** | The copy-editor of the item translation (AuthorResource id) | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**extra** | **object** | Extra data for translation (object, may be null) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**item** | [**ItemResource**](ItemResource.md) | The item relationship (ItemResource) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The language relationship (LanguageResource) | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The context relationship (ContextResource) | [optional] [default to undefined]
**author** | [**AuthorResource**](AuthorResource.md) | The author relationship (AuthorResource) | [optional] [default to undefined]
**text_copy_editor** | [**AuthorResource**](AuthorResource.md) | The copy-editor relationship (AuthorResource) | [optional] [default to undefined]
**translator** | [**AuthorResource**](AuthorResource.md) | The translator relationship (AuthorResource) | [optional] [default to undefined]
**translation_copy_editor** | [**AuthorResource**](AuthorResource.md) | The translation copy-editor relationship (AuthorResource) | [optional] [default to undefined]

## Example

```typescript
import { ItemTranslationResource } from './api';

const instance: ItemTranslationResource = {
    id,
    item_id,
    language_id,
    context_id,
    name,
    alternate_name,
    description,
    type,
    holder,
    owner,
    initial_owner,
    dates,
    location,
    dimensions,
    place_of_production,
    method_for_datation,
    method_for_provenance,
    obtention,
    bibliography,
    author_id,
    text_copy_editor_id,
    translator_id,
    translation_copy_editor_id,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
    item,
    language,
    context,
    author,
    text_copy_editor,
    translator,
    translation_copy_editor,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
