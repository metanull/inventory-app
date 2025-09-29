# CollectionResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**type** | **string** | The type of collection: \&#39;collection\&#39;, \&#39;exhibition\&#39;, or \&#39;gallery\&#39; | [default to undefined]
**language_id** | **string** | The language this collection belongs to (LanguageResource id) | [default to undefined]
**context_id** | **string** | The context this collection belongs to (ContextResource id) | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The language relationship (LanguageResource) | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The context relationship (ContextResource) | [optional] [default to undefined]
**translations** | [**Array&lt;CollectionTranslationResource&gt;**](CollectionTranslationResource.md) | Translations for this collection (CollectionTranslationResource[]) | [optional] [default to undefined]
**partners** | [**Array&lt;PartnerResource&gt;**](PartnerResource.md) | Partners associated with this collection (PartnerResource[]) | [optional] [default to undefined]
**items** | [**Array&lt;ItemResource&gt;**](ItemResource.md) | Items associated with this collection - primary relationship (ItemResource[]) | [optional] [default to undefined]
**attachedItems** | [**Array&lt;ItemResource&gt;**](ItemResource.md) | Items attached to this collection via many-to-many relationship (ItemResource[]) | [optional] [default to undefined]
**items_count** | **string** | The number of items in this collection (computed) | [optional] [default to undefined]
**attached_items_count** | **string** |  | [optional] [default to undefined]
**partners_count** | **string** |  | [optional] [default to undefined]
**translations_count** | **string** |  | [optional] [default to undefined]

## Example

```typescript
import { CollectionResource } from './api';

const instance: CollectionResource = {
    id,
    internal_name,
    type,
    language_id,
    context_id,
    backward_compatibility,
    created_at,
    updated_at,
    language,
    context,
    translations,
    partners,
    items,
    attachedItems,
    items_count,
    attached_items_count,
    partners_count,
    translations_count,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
