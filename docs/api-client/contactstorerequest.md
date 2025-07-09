---
layout: default
title: "ContactStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# ContactStoreRequest

## Properties

| Name              | Type                                                                                             | Description | Notes                             |
| ----------------- | ------------------------------------------------------------------------------------------------ | ----------- | --------------------------------- |
| **internal_name** | **string**                                                                                       |             | [default to undefined]            |
| **phone_number**  | **string**                                                                                       |             | [optional] [default to undefined] |
| **fax_number**    | **string**                                                                                       |             | [optional] [default to undefined] |
| **email**         | **string**                                                                                       |             | [optional] [default to undefined] |
| **translations**  | [**Array&lt;ContactStoreRequestTranslationsInner&gt;**](ContactStoreRequestTranslationsInner.md) |             | [default to undefined]            |

## Example

```typescript
import { ContactStoreRequest } from "./api";

const instance: ContactStoreRequest = {
  internal_name,
  phone_number,
  fax_number,
  email,
  translations,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
