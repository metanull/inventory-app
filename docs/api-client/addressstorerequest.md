---
layout: default
title: "AddressStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# AddressStoreRequest

## Properties

| Name              | Type                                                                                             | Description | Notes                             |
| ----------------- | ------------------------------------------------------------------------------------------------ | ----------- | --------------------------------- |
| **id**            | **string**                                                                                       |             | [optional] [default to undefined] |
| **internal_name** | **string**                                                                                       |             | [default to undefined]            |
| **country_id**    | **number**                                                                                       |             | [default to undefined]            |
| **translations**  | [**Array&lt;AddressStoreRequestTranslationsInner&gt;**](AddressStoreRequestTranslationsInner.md) |             | [optional] [default to undefined] |

## Example

```typescript
import { AddressStoreRequest } from "./api";

const instance: AddressStoreRequest = {
  id,
  internal_name,
  country_id,
  translations,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
