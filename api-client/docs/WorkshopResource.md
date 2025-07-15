# WorkshopResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**name** | **string** | The name of the workshop | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**items** | [**Array&lt;ItemResource&gt;**](ItemResource.md) | Items associated with this workshop (ItemResource[]) | [optional] [default to undefined]

## Example

```typescript
import { WorkshopResource } from './api';

const instance: WorkshopResource = {
    id,
    name,
    internal_name,
    backward_compatibility,
    created_at,
    updated_at,
    items,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
