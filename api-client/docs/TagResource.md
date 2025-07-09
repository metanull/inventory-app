# TagResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the tag (GUID) | [default to undefined]
**internal_name** | **string** | The name of the tag, it shall only be used internally | [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this tag corresponds to a legacy tag from the previous database, nullable | [default to undefined]
**description** | **string** | The description of the tag | [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { TagResource } from './api';

const instance: TagResource = {
    id,
    internal_name,
    backward_compatibility,
    description,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
