# CountryResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the country (ISO 3166-1 alpha-3 code) | [default to undefined]
**internal_name** | **string** | The name of the country, it shall only be used internally | [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this country corresponds to a legacy country from the MWNF3 database, nullable | [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { CountryResource } from './api';

const instance: CountryResource = {
    id,
    internal_name,
    backward_compatibility,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
