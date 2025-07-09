---
layout: default
title: "PictureTranslationUpdateRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# PictureTranslationUpdateRequest

## Properties

| Name                           | Type                    | Description | Notes                             |
| ------------------------------ | ----------------------- | ----------- | --------------------------------- |
| **picture_id**                 | **string**              |             | [optional] [default to undefined] |
| **language_id**                | **string**              |             | [optional] [default to undefined] |
| **context_id**                 | **string**              |             | [optional] [default to undefined] |
| **description**                | **string**              |             | [optional] [default to undefined] |
| **caption**                    | **string**              |             | [optional] [default to undefined] |
| **author_id**                  | **string**              |             | [optional] [default to undefined] |
| **text_copy_editor_id**        | **string**              |             | [optional] [default to undefined] |
| **translator_id**              | **string**              |             | [optional] [default to undefined] |
| **translation_copy_editor_id** | **string**              |             | [optional] [default to undefined] |
| **backward_compatibility**     | **string**              |             | [optional] [default to undefined] |
| **extra**                      | **Array&lt;string&gt;** |             | [optional] [default to undefined] |

## Example

```typescript
import { PictureTranslationUpdateRequest } from "./api";

const instance: PictureTranslationUpdateRequest = {
  picture_id,
  language_id,
  context_id,
  description,
  caption,
  author_id,
  text_copy_editor_id,
  translator_id,
  translation_copy_editor_id,
  backward_compatibility,
  extra,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
