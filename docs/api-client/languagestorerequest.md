---
layout: default
title: "LanguageStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# LanguageStoreRequest

## Properties

| Name                       | Type        | Description | Notes                             |
| -------------------------- | ----------- | ----------- | --------------------------------- |
| **id**                     | **string**  |             | [default to undefined]            |
| **internal_name**          | **string**  |             | [default to undefined]            |
| **backward_compatibility** | **string**  |             | [optional] [default to undefined] |
| **is_default**             | **boolean** |             | [optional] [default to undefined] |

## Example

```typescript
import { LanguageStoreRequest } from "./api";

const instance: LanguageStoreRequest = {
  id,
  internal_name,
  backward_compatibility,
  is_default,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
