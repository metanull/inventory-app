---
layout: default
title: "ImageUploadResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# ImageUploadResource

## Properties

| Name           | Type       | Description                                 | Notes                  |
| -------------- | ---------- | ------------------------------------------- | ---------------------- |
| **id**         | **string** | The unique identifier of the picture (GUID) | [default to undefined] |
| **path**       | **string** | The path to the picture file                | [default to undefined] |
| **name**       | **string** | The original name of the uploaded file      | [default to undefined] |
| **extension**  | **string** | The file extension of the uploaded file     | [default to undefined] |
| **mime_type**  | **string** | The MIME type of the uploaded file          | [default to undefined] |
| **size**       | **number** | The size of the uploaded file in bytes      | [default to undefined] |
| **created_at** | **string** | Date of creation                            | [default to undefined] |
| **updated_at** | **string** | Date of last modification                   | [default to undefined] |

## Example

```typescript
import { ImageUploadResource } from "./api";

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

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
