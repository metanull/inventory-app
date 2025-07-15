# ThemeResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**exhibition_id** | **string** | The exhibition this theme belongs to (ExhibitionResource id) | [default to undefined]
**parent_id** | **string** | The parent theme of this theme (ThemeResource id) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**translations** | [**Array&lt;ThemeTranslationResource&gt;**](ThemeTranslationResource.md) | Translations for this theme (ThemeTranslationResource[]) | [optional] [default to undefined]
**subthemes** | [**Array&lt;ThemeResource&gt;**](ThemeResource.md) | Subthemes of this theme (ThemeResource[]) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ThemeResource } from './api';

const instance: ThemeResource = {
    id,
    exhibition_id,
    parent_id,
    internal_name,
    backward_compatibility,
    translations,
    subthemes,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
