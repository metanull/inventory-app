# ItemItemLinkResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**source_id** | **string** | The source item ID (the item initiating the link) | [default to undefined]
**target_id** | **string** | The target item ID (the item receiving the link) | [default to undefined]
**context_id** | **string** | The context ID (the context in which the link exists) | [default to undefined]
**source** | [**ItemResource**](ItemResource.md) | The source item (ItemResource) | [optional] [default to undefined]
**target** | [**ItemResource**](ItemResource.md) | The target item (ItemResource) | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The context (ContextResource) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ItemItemLinkResource } from './api';

const instance: ItemItemLinkResource = {
    id,
    source_id,
    target_id,
    context_id,
    source,
    target,
    context,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
