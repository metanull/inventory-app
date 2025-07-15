# AddressTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**address_id** | **string** | The address this translation belongs to (AddressResource id) | [default to undefined]
**language_id** | **string** | The language of this translation (LanguageResource id) | [default to undefined]
**address** | **string** | The address translation text | [default to undefined]
**description** | **string** | The description of the address translation | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { AddressTranslationResource } from './api';

const instance: AddressTranslationResource = {
    id,
    address_id,
    language_id,
    address,
    description,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
