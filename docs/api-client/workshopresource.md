---
layout: default
title: "WorkshopResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# WorkshopResource

## Properties

| Name                       | Type                                             | Description | Notes                             |
| -------------------------- | ------------------------------------------------ | ----------- | --------------------------------- |
| **id**                     | **string**                                       |             | [default to undefined]            |
| **name**                   | **string**                                       |             | [default to undefined]            |
| **internal_name**          | **string**                                       |             | [default to undefined]            |
| **backward_compatibility** | **string**                                       |             | [default to undefined]            |
| **created_at**             | **string**                                       |             | [default to undefined]            |
| **updated_at**             | **string**                                       |             | [default to undefined]            |
| **items**                  | [**Array&lt;ItemResource&gt;**](ItemResource.md) |             | [optional] [default to undefined] |

## Example

```typescript
import { WorkshopResource } from "./api";

const instance: WorkshopResource = {
  id,
  name,
  internal_name,
  backward_compatibility,
  created_at,
  updated_at,
  items,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
