# DetailResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**item** | [**ItemResource**](ItemResource.md) | The item this detail belongs to (ItemResource) | [optional] [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**translations** | [**Array&lt;DetailTranslationResource&gt;**](DetailTranslationResource.md) | Translations for this detail (internationalization and contextualization) (DetailTranslationResource[]) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { DetailResource } from './api';

const instance: DetailResource = {
    id,
    internal_name,
    item,
    backward_compatibility,
    translations,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
