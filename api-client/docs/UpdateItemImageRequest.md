# UpdateItemImageRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**original_name** | **string** | Path and item_id are immutable - not allowed in updates | [optional] [default to undefined]
**mime_type** | **string** |  | [optional] [default to undefined]
**size** | **number** |  | [optional] [default to undefined]
**alt_text** | **string** |  | [optional] [default to undefined]
**display_order** | **number** |  | [optional] [default to undefined]
**include** | **string** | Comma-separated list of related resources to include. Valid values: &#x60;item&#x60;. | [optional] [default to undefined]

## Example

```typescript
import { UpdateItemImageRequest } from './api';

const instance: UpdateItemImageRequest = {
    original_name,
    mime_type,
    size,
    alt_text,
    display_order,
    include,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
