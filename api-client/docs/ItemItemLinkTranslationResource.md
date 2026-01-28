# ItemItemLinkTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**item_item_link_id** | **string** | The item-item link this translation belongs to (ItemItemLinkResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**description** | **string** | The description of the link (source → target direction) | [default to undefined]
**reciprocal_description** | **string** | The reciprocal description of the link (target → source direction) | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any) | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**item_item_link** | [**ItemItemLinkResource**](ItemItemLinkResource.md) | The item-item link relationship (ItemItemLinkResource) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The language relationship (LanguageResource) | [optional] [default to undefined]

## Example

```typescript
import { ItemItemLinkTranslationResource } from './api';

const instance: ItemItemLinkTranslationResource = {
    id,
    item_item_link_id,
    language_id,
    description,
    reciprocal_description,
    backward_compatibility,
    created_at,
    updated_at,
    item_item_link,
    language,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
