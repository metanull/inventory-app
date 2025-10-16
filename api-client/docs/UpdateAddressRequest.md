# UpdateAddressRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [optional] [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**country_id** | **number** |  | [default to undefined]
**backward_compatibility** | **string** |  | [optional] [default to undefined]
**translations** | [**Array&lt;UpdateAddressRequestTranslationsInner&gt;**](UpdateAddressRequestTranslationsInner.md) |  | [optional] [default to undefined]

## Example

```typescript
import { UpdateAddressRequest } from './api';

const instance: UpdateAddressRequest = {
    id,
    internal_name,
    country_id,
    backward_compatibility,
    translations,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
