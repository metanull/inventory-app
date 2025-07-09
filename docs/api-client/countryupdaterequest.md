---
layout: default
title: "CountryUpdateRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# CountryUpdateRequest

## Properties

| Name                       | Type       | Description | Notes                             |
| -------------------------- | ---------- | ----------- | --------------------------------- |
| **internal_name**          | **string** |             | [default to undefined]            |
| **backward_compatibility** | **string** |             | [optional] [default to undefined] |

## Example

```typescript
import { CountryUpdateRequest } from "./api";

const instance: CountryUpdateRequest = {
  internal_name,
  backward_compatibility,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
