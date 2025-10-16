# UpdateContactRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**internal_name** | **string** |  | [default to undefined]
**phone_number** | **string** |  | [optional] [default to undefined]
**fax_number** | **string** |  | [optional] [default to undefined]
**email** | **string** |  | [optional] [default to undefined]
**backward_compatibility** | **string** |  | [optional] [default to undefined]
**translations** | [**Array&lt;UpdateContactRequestTranslationsInner&gt;**](UpdateContactRequestTranslationsInner.md) |  | [optional] [default to undefined]

## Example

```typescript
import { UpdateContactRequest } from './api';

const instance: UpdateContactRequest = {
    internal_name,
    phone_number,
    fax_number,
    email,
    backward_compatibility,
    translations,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
