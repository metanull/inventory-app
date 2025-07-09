---
layout: default
title: "LanguageResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# LanguageResource

## Properties

| Name                       | Type        | Description                                                                                                  | Notes                  |
| -------------------------- | ----------- | ------------------------------------------------------------------------------------------------------------ | ---------------------- |
| **id**                     | **string**  | The unique identifier of the language (ISO 639-1 code)                                                       | [default to undefined] |
| **internal_name**          | **string**  | The name of the language, it shall only be used internally                                                   | [default to undefined] |
| **backward_compatibility** | **string**  | The legacy Id when this language corresponds to a legacy language from the MWNF3 database, nullable          | [default to undefined] |
| **is_default**             | **boolean** | Indicates if this language is the default one. There is one single default language for the entire database. | [default to undefined] |
| **created_at**             | **string**  | Date of creation                                                                                             | [default to undefined] |
| **updated_at**             | **string**  | Date of last modification                                                                                    | [default to undefined] |

## Example

```typescript
import { LanguageResource } from "./api";

const instance: LanguageResource = {
  id,
  internal_name,
  backward_compatibility,
  is_default,
  created_at,
  updated_at,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
