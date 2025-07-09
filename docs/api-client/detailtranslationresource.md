---
layout: default
title: "DetailTranslationResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# DetailTranslationResource

## Properties

| Name                           | Type                                        | Description       | Notes                             |
| ------------------------------ | ------------------------------------------- | ----------------- | --------------------------------- |
| **id**                         | **string**                                  |                   | [default to undefined]            |
| **detail_id**                  | **string**                                  |                   | [default to undefined]            |
| **language_id**                | **string**                                  |                   | [default to undefined]            |
| **context_id**                 | **string**                                  |                   | [default to undefined]            |
| **name**                       | **string**                                  |                   | [default to undefined]            |
| **alternate_name**             | **string**                                  |                   | [default to undefined]            |
| **description**                | **string**                                  |                   | [default to undefined]            |
| **author_id**                  | **string**                                  |                   | [default to undefined]            |
| **text_copy_editor_id**        | **string**                                  |                   | [default to undefined]            |
| **translator_id**              | **string**                                  |                   | [default to undefined]            |
| **translation_copy_editor_id** | **string**                                  |                   | [default to undefined]            |
| **backward_compatibility**     | **string**                                  |                   | [default to undefined]            |
| **extra**                      | **Array&lt;any&gt;**                        |                   | [default to undefined]            |
| **created_at**                 | **string**                                  |                   | [default to undefined]            |
| **updated_at**                 | **string**                                  |                   | [default to undefined]            |
| **detail**                     | [**DetailResource**](DetailResource.md)     | Relationship data | [optional] [default to undefined] |
| **language**                   | [**LanguageResource**](LanguageResource.md) |                   | [optional] [default to undefined] |
| **context**                    | [**ContextResource**](ContextResource.md)   |                   | [optional] [default to undefined] |
| **author**                     | [**AuthorResource**](AuthorResource.md)     |                   | [optional] [default to undefined] |
| **text_copy_editor**           | [**AuthorResource**](AuthorResource.md)     |                   | [optional] [default to undefined] |
| **translator**                 | [**AuthorResource**](AuthorResource.md)     |                   | [optional] [default to undefined] |
| **translation_copy_editor**    | [**AuthorResource**](AuthorResource.md)     |                   | [optional] [default to undefined] |

## Example

```typescript
import { DetailTranslationResource } from "./api";

const instance: DetailTranslationResource = {
  id,
  detail_id,
  language_id,
  context_id,
  name,
  alternate_name,
  description,
  author_id,
  text_copy_editor_id,
  translator_id,
  translation_copy_editor_id,
  backward_compatibility,
  extra,
  created_at,
  updated_at,
  detail,
  language,
  context,
  author,
  text_copy_editor,
  translator,
  translation_copy_editor,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
