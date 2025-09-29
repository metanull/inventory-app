# ItemImageResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**item_id** | **string** | The item this image belongs to | [default to undefined]
**path** | **string** | The path to the image file | [default to undefined]
**original_name** | **string** | The original filename when uploaded | [default to undefined]
**mime_type** | **string** | The MIME type of the image | [default to undefined]
**size** | **number** | The file size in bytes | [default to undefined]
**alt_text** | **string** | Alternative text for accessibility | [default to undefined]
**display_order** | **number** | Display order for sorting images | [default to undefined]
**item** | [**ItemResource**](ItemResource.md) | The item this image belongs to (ItemResource) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ItemImageResource } from './api';

const instance: ItemImageResource = {
    id,
    item_id,
    path,
    original_name,
    mime_type,
    size,
    alt_text,
    display_order,
    item,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
