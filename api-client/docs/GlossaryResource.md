# GlossaryResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]
**translations** | [**Array&lt;GlossaryTranslationResource&gt;**](GlossaryTranslationResource.md) | Relationships (only included if loaded) | [optional] [default to undefined]
**spellings** | [**Array&lt;GlossarySpellingResource&gt;**](GlossarySpellingResource.md) |  | [optional] [default to undefined]
**synonyms** | [**Array&lt;GlossaryResource&gt;**](GlossaryResource.md) |  | [optional] [default to undefined]

## Example

```typescript
import { GlossaryResource } from './api';

const instance: GlossaryResource = {
    id,
    internal_name,
    backward_compatibility,
    created_at,
    updated_at,
    translations,
    spellings,
    synonyms,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
