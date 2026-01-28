# UpdateCollectionImageRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**original_name** | **string** | Path and collection_id are immutable - not allowed in updates | [optional] [default to undefined]
**mime_type** | **string** |  | [optional] [default to undefined]
**size** | **number** |  | [optional] [default to undefined]
**alt_text** | **string** |  | [optional] [default to undefined]
**display_order** | **number** |  | [optional] [default to undefined]
**include** | **string** | Comma-separated list of related resources to include. Valid values: &#x60;collection&#x60;. | [optional] [default to undefined]

## Example

```typescript
import { UpdateCollectionImageRequest } from './api';

const instance: UpdateCollectionImageRequest = {
    original_name,
    mime_type,
    size,
    alt_text,
    display_order,
    include,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
