# PictureResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**path** | **string** | The path to the picture file | [default to undefined]
**copyright_text** | **string** | The copyright text associated with the picture | [default to undefined]
**copyright_url** | **string** | The URL for the copyright information | [default to undefined]
**upload_name** | **string** | The original name of the uploaded file | [default to undefined]
**upload_extension** | **string** | The file extension of the uploaded file | [default to undefined]
**upload_mime_type** | **string** | The MIME type of the uploaded file | [default to undefined]
**upload_size** | **number** | The size of the uploaded file in bytes | [default to undefined]
**pictureable_type** | **string** | The type of the parent model (Item, Detail, Partner) | [default to undefined]
**pictureable_id** | **string** | The ID of the parent model | [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { PictureResource } from './api';

const instance: PictureResource = {
    id,
    internal_name,
    backward_compatibility,
    path,
    copyright_text,
    copyright_url,
    upload_name,
    upload_extension,
    upload_mime_type,
    upload_size,
    pictureable_type,
    pictureable_id,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
