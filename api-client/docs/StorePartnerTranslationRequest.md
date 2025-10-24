# StorePartnerTranslationRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [optional] [default to undefined]
**partner_id** | **string** |  | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**context_id** | **string** |  | [default to undefined]
**name** | **string** |  | [default to undefined]
**description** | **string** |  | [optional] [default to undefined]
**city_display** | **string** | Address fields | [optional] [default to undefined]
**address_line_1** | **string** |  | [optional] [default to undefined]
**address_line_2** | **string** |  | [optional] [default to undefined]
**postal_code** | **string** |  | [optional] [default to undefined]
**address_notes** | **string** |  | [optional] [default to undefined]
**contact_name** | **string** | Contact fields | [optional] [default to undefined]
**contact_email_general** | **string** |  | [optional] [default to undefined]
**contact_email_press** | **string** |  | [optional] [default to undefined]
**contact_phone** | **string** |  | [optional] [default to undefined]
**contact_website** | **string** |  | [optional] [default to undefined]
**contact_notes** | **string** |  | [optional] [default to undefined]
**backward_compatibility** | **string** | Metadata | [optional] [default to undefined]
**extra** | **Array&lt;string&gt;** |  | [optional] [default to undefined]
**contact_emails** | **Array&lt;string&gt;** |  | [optional] [default to undefined]
**contact_phones** | **Array&lt;string&gt;** |  | [optional] [default to undefined]

## Example

```typescript
import { StorePartnerTranslationRequest } from './api';

const instance: StorePartnerTranslationRequest = {
    id,
    partner_id,
    language_id,
    context_id,
    name,
    description,
    city_display,
    address_line_1,
    address_line_2,
    postal_code,
    address_notes,
    contact_name,
    contact_email_general,
    contact_email_press,
    contact_phone,
    contact_website,
    contact_notes,
    backward_compatibility,
    extra,
    contact_emails,
    contact_phones,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
