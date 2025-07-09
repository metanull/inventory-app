---
layout: default
title: "CountryResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# CountryResource

## Properties

| Name                       | Type       | Description                                                                                       | Notes                  |
| -------------------------- | ---------- | ------------------------------------------------------------------------------------------------- | ---------------------- |
| **id**                     | **string** | The unique identifier of the country (ISO 3166-1 alpha-3 code)                                    | [default to undefined] |
| **internal_name**          | **string** | The name of the country, it shall only be used internally                                         | [default to undefined] |
| **backward_compatibility** | **string** | The legacy Id when this country corresponds to a legacy country from the MWNF3 database, nullable | [default to undefined] |
| **created_at**             | **string** | Date of creation                                                                                  | [default to undefined] |
| **updated_at**             | **string** | Date of last modification                                                                         | [default to undefined] |

## Example

```typescript
import { CountryResource } from "./api";

const instance: CountryResource = {
  id,
  internal_name,
  backward_compatibility,
  created_at,
  updated_at,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
