# CountryTranslationResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**country_id** | **string** |  | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**name** | **string** |  | [default to undefined]
**country** | [**CountryResource**](CountryResource.md) | Relationships | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) |  | [optional] [default to undefined]
**backward_compatibility** | **string** | Metadata | [default to undefined]
**extra** | **object** |  | [default to undefined]
**created_at** | **string** | Timestamps | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { CountryTranslationResource } from './api';

const instance: CountryTranslationResource = {
    id,
    country_id,
    language_id,
    name,
    country,
    language,
    backward_compatibility,
    extra,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
