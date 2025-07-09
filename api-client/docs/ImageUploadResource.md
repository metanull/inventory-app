# ImageUploadResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the picture (GUID) | [default to undefined]
**path** | **string** | The path to the picture file | [default to undefined]
**name** | **string** | The original name of the uploaded file | [default to undefined]
**extension** | **string** | The file extension of the uploaded file | [default to undefined]
**mime_type** | **string** | The MIME type of the uploaded file | [default to undefined]
**size** | **number** | The size of the uploaded file in bytes | [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { ImageUploadResource } from './api';

const instance: ImageUploadResource = {
    id,
    path,
    name,
    extension,
    mime_type,
    size,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
