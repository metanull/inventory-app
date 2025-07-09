---
layout: default
title: "CollectionStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# CollectionStoreRequest

## Properties

| Name                       | Type       | Description | Notes                             |
| -------------------------- | ---------- | ----------- | --------------------------------- |
| **internal_name**          | **string** |             | [default to undefined]            |
| **language_id**            | **string** |             | [default to undefined]            |
| **context_id**             | **string** |             | [default to undefined]            |
| **backward_compatibility** | **string** |             | [optional] [default to undefined] |

## Example

```typescript
import { CollectionStoreRequest } from "./api";

const instance: CollectionStoreRequest = {
  internal_name,
  language_id,
  context_id,
  backward_compatibility,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
