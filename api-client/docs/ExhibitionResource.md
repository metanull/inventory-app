# ExhibitionResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**translations** | [**Array&lt;ExhibitionTranslationResource&gt;**](ExhibitionTranslationResource.md) | Translations for this exhibition (ExhibitionTranslationResource[]) | [optional] [default to undefined]
**partners** | [**Array&lt;PartnerResource&gt;**](PartnerResource.md) | Partners associated with this exhibition (PartnerResource[]) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

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
