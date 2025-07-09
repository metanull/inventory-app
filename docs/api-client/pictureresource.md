---
layout: default
title: "PictureResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# PictureResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the picture (UUID) | [default to undefined]
**internal_name** | **string** | The internal name of the picture | [default to undefined]
**backward_compatibility** | **string** | The legacy ID when this picture corresponds to a legacy image from the MWNF3 database, nullable | [default to undefined]
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

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
