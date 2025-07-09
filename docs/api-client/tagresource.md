---
layout: default
title: "TagResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# TagResource

## Properties

| Name                       | Type       | Description                                                                                  | Notes                  |
| -------------------------- | ---------- | -------------------------------------------------------------------------------------------- | ---------------------- |
| **id**                     | **string** | The unique identifier of the tag (GUID)                                                      | [default to undefined] |
| **internal_name**          | **string** | The name of the tag, it shall only be used internally                                        | [default to undefined] |
| **backward_compatibility** | **string** | The legacy Id when this tag corresponds to a legacy tag from the previous database, nullable | [default to undefined] |
| **description**            | **string** | The description of the tag                                                                   | [default to undefined] |
| **created_at**             | **string** | Date of creation                                                                             | [default to undefined] |
| **updated_at**             | **string** | Date of last modification                                                                    | [default to undefined] |

## Example

```typescript
import { TagResource } from "./api";

const instance: TagResource = {
  id,
  internal_name,
  backward_compatibility,
  description,
  created_at,
  updated_at,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
