---
layout: default
title: "AddressUpdateRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# AddressUpdateRequest

## Properties

| Name              | Type                                                                                               | Description | Notes                             |
| ----------------- | -------------------------------------------------------------------------------------------------- | ----------- | --------------------------------- |
| **id**            | **string**                                                                                         |             | [optional] [default to undefined] |
| **internal_name** | **string**                                                                                         |             | [default to undefined]            |
| **country_id**    | **number**                                                                                         |             | [default to undefined]            |
| **translations**  | [**Array&lt;AddressUpdateRequestTranslationsInner&gt;**](AddressUpdateRequestTranslationsInner.md) |             | [optional] [default to undefined] |

## Example

```typescript
import { AddressUpdateRequest } from "./api";

const instance: AddressUpdateRequest = {
  id,
  internal_name,
  country_id,
  translations,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
