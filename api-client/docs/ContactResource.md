# ContactResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**phone_number** | **string** | The phone number of the contact | [default to undefined]
**formatted_phone_number** | **string** | The formatted phone number of the contact | [default to undefined]
**fax_number** | **string** | The fax number of the contact | [default to undefined]
**formatted_fax_number** | **string** | The formatted fax number of the contact | [default to undefined]
**email** | **string** | The email address of the contact | [default to undefined]
**translations** | [**Array&lt;ContactTranslationResource&gt;**](ContactTranslationResource.md) | Translations for this contact (ContactTranslationResource[]) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ContactResource } from './api';

const instance: ContactResource = {
    id,
    internal_name,
    phone_number,
    formatted_phone_number,
    fax_number,
    formatted_fax_number,
    email,
    translations,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
