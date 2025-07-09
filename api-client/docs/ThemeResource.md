# ThemeResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**exhibition_id** | **string** |  | [default to undefined]
**parent_id** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [default to undefined]
**translations** | [**Array&lt;ThemeTranslationResource&gt;**](ThemeTranslationResource.md) |  | [optional] [default to undefined]
**subthemes** | [**Array&lt;ThemeResource&gt;**](ThemeResource.md) |  | [optional] [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

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
