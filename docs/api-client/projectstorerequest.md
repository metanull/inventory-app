---
layout: default
title: "ProjectStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# ProjectStoreRequest

## Properties

| Name                       | Type        | Description | Notes                             |
| -------------------------- | ----------- | ----------- | --------------------------------- |
| **internal_name**          | **string**  |             | [default to undefined]            |
| **backward_compatibility** | **string**  |             | [optional] [default to undefined] |
| **launch_date**            | **string**  |             | [optional] [default to undefined] |
| **is_launched**            | **boolean** |             | [optional] [default to undefined] |
| **is_enabled**             | **boolean** |             | [optional] [default to undefined] |
| **context_id**             | **string**  |             | [optional] [default to undefined] |
| **language_id**            | **string**  |             | [optional] [default to undefined] |

## Example

```typescript
import { ProjectStoreRequest } from "./api";

const instance: ProjectStoreRequest = {
  internal_name,
  backward_compatibility,
  launch_date,
  is_launched,
  is_enabled,
  context_id,
  language_id,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
