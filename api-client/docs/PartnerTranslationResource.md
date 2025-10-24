# PartnerTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**partner_id** | **string** | Foreign keys | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**context_id** | **string** |  | [default to undefined]
**name** | **string** | Core partner info | [default to undefined]
**description** | **string** |  | [default to undefined]
**city_display** | **string** | Address fields (embedded) | [default to undefined]
**address_line_1** | **string** |  | [default to undefined]
**address_line_2** | **string** |  | [default to undefined]
**postal_code** | **string** |  | [default to undefined]
**address_notes** | **string** |  | [default to undefined]
**contact_name** | **string** | Contact fields (semi-structured) | [default to undefined]
**contact_email_general** | **string** |  | [default to undefined]
**contact_email_press** | **string** |  | [default to undefined]
**contact_phone** | **string** |  | [default to undefined]
**contact_website** | **string** |  | [default to undefined]
**contact_notes** | **string** |  | [default to undefined]
**contact_emails** | **string** |  | [default to undefined]
**contact_phones** | **string** |  | [default to undefined]
**partner** | [**PartnerResource**](PartnerResource.md) | Relationships | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) |  | [optional] [default to undefined]
**context** | [**ContextResource**](ContextResource.md) |  | [optional] [default to undefined]
**partner_translation_images** | [**Array&lt;PartnerTranslationImageResource&gt;**](PartnerTranslationImageResource.md) |  | [optional] [default to undefined]
**backward_compatibility** | **string** | Metadata | [default to undefined]
**extra** | **string** |  | [default to undefined]
**created_at** | **string** | Timestamps | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { PartnerTranslationResource } from './api';

const instance: PartnerTranslationResource = {
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
    contact_emails,
    contact_phones,
    partner,
    language,
    context,
    partner_translation_images,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
