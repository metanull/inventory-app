# ExhibitionResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [default to undefined]
**translations** | [**Array&lt;ExhibitionTranslationResource&gt;**](ExhibitionTranslationResource.md) |  | [optional] [default to undefined]
**partners** | [**Array&lt;PartnerResource&gt;**](PartnerResource.md) |  | [optional] [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { ExhibitionResource } from './api';

const instance: ExhibitionResource = {
    id,
    internal_name,
    backward_compatibility,
    translations,
    partners,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
