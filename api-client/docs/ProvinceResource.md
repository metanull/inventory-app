# ProvinceResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**country_id** | **string** | The country this province belongs to (CountryResource id) | [default to undefined]
**translations** | [**Array&lt;ProvinceTranslationResource&gt;**](ProvinceTranslationResource.md) | Translations for this province (ProvinceTranslationResource[]) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ProvinceResource } from './api';

const instance: ProvinceResource = {
    id,
    internal_name,
    country_id,
    translations,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
