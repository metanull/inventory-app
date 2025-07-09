---
layout: default
title: "LocationStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# LocationStoreRequest

## Properties

| Name              | Type                                                                                               | Description | Notes                  |
| ----------------- | -------------------------------------------------------------------------------------------------- | ----------- | ---------------------- |
| **internal_name** | **string**                                                                                         |             | [default to undefined] |
| **country_id**    | **number**                                                                                         |             | [default to undefined] |
| **translations**  | [**Array&lt;LocationStoreRequestTranslationsInner&gt;**](LocationStoreRequestTranslationsInner.md) |             | [default to undefined] |

## Example

```typescript
import { LocationStoreRequest } from "./api";

const instance: LocationStoreRequest = {
  internal_name,
  country_id,
  translations,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
