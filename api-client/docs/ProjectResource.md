# ProjectResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the project (GUID) | [default to undefined]
**internal_name** | **string** | The name of the project, it shall only be used internally | [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this project corresponds to a legacy project from the MWNF3 database, nullable | [default to undefined]
**launch_date** | **string** | Launch date of the project, nullable | [default to undefined]
**is_launched** | **boolean** | Indicates if the project has been launched already | [default to undefined]
**is_enabled** | **boolean** | Indicates if the project is enabled (active) | [default to undefined]
**context** | [**ContextResource**](ContextResource.md) | The default context used within the project | [optional] [default to undefined]
**language** | [**LanguageResource**](LanguageResource.md) | The default language used within the project | [optional] [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

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
