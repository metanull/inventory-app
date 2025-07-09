---
layout: default
title: "LocationUpdateRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# LocationUpdateRequest

## Properties

| Name              | Type                                                                                                 | Description | Notes                             |
| ----------------- | ---------------------------------------------------------------------------------------------------- | ----------- | --------------------------------- |
| **internal_name** | **string**                                                                                           |             | [default to undefined]            |
| **country_id**    | **number**                                                                                           |             | [default to undefined]            |
| **translations**  | [**Array&lt;LocationUpdateRequestTranslationsInner&gt;**](LocationUpdateRequestTranslationsInner.md) |             | [optional] [default to undefined] |

## Example

```typescript
import { LocationUpdateRequest } from "./api";

const instance: LocationUpdateRequest = {
  internal_name,
  country_id,
  translations,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
