# ProjectResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**launch_date** | **string** | Launch date of the project, nullable | [default to undefined]
**is_launched** | **boolean** | Indicates if the project has been launched already | [default to undefined]
**is_enabled** | **boolean** | Indicates if the project is enabled (active) | [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The default context used within the project (ContextResource) | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The default language used within the project (LanguageResource) | [optional] [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { ProjectResource } from './api';

const instance: ProjectResource = {
    id,
    internal_name,
    backward_compatibility,
    launch_date,
    is_launched,
    is_enabled,
    context,
    language,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
