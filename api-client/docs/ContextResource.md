# ContextResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the context (GUID) | [default to undefined]
**internal_name** | **string** | The name of the context, it shall only be used internally | [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this context corresponds to a legacy context from the MWNF3 database, nullable | [default to undefined]
**is_default** | **boolean** | Indicates if this context is the default one. There is one single default context for the entire database. | [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { ContextResource } from './api';

const instance: ContextResource = {
    id,
    internal_name,
    backward_compatibility,
    is_default,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
