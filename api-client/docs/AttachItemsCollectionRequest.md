# AttachItemsCollectionRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**include** | **string** | Comma-separated list of related resources to include. Valid values: &#x60;language&#x60;, &#x60;context&#x60;, &#x60;translations&#x60;, &#x60;partners&#x60;, &#x60;items&#x60;, &#x60;attachedItems&#x60;, &#x60;collectionImages&#x60;. | [optional] [default to undefined]
**item_ids** | **Array&lt;string&gt;** |  | [default to undefined]

## Example

```typescript
import { AttachItemsCollectionRequest } from './api';

const instance: AttachItemsCollectionRequest = {
    include,
    item_ids,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
