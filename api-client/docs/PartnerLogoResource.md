# PartnerLogoResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**partner_id** | **string** | Foreign key | [default to undefined]
**path** | **string** | Logo information | [default to undefined]
**original_name** | **string** |  | [default to undefined]
**mime_type** | **string** |  | [default to undefined]
**size** | **number** |  | [default to undefined]
**logo_type** | **string** |  | [default to undefined]
**alt_text** | **string** |  | [default to undefined]
**display_order** | **number** |  | [default to undefined]
**partner** | [**PartnerResource**](PartnerResource.md) | Relationships | [optional] [default to undefined]
**created_at** | **string** | Timestamps | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { PartnerLogoResource } from './api';

const instance: PartnerLogoResource = {
    id,
    partner_id,
    path,
    original_name,
    mime_type,
    size,
    logo_type,
    alt_text,
    display_order,
    partner,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
