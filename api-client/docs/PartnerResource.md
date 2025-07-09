# PartnerResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the partner (GUID) | [default to undefined]
**internal_name** | **string** | The name of the partner, it shall only be used internally | [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this partner corresponds to a legacy partner from the MWNF3 database, nullable | [default to undefined]
**type** | **string** | The type of the partner, either \&#39;museum\&#39;,  \&#39;institution\&#39; or \&#39;individual\&#39; | [default to undefined]
**country** | [**CountryResource**](CountryResource.md) | The country this partner is associated with, nullable | [optional] [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { PartnerResource } from './api';

const instance: PartnerResource = {
    id,
    internal_name,
    backward_compatibility,
    type,
    country,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
