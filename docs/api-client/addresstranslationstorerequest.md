---
layout: default
title: "AddressTranslationStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# AddressTranslationStoreRequest

## Properties

| Name            | Type       | Description | Notes                             |
| --------------- | ---------- | ----------- | --------------------------------- |
| **address_id**  | **string** |             | [default to undefined]            |
| **language_id** | **string** |             | [default to undefined]            |
| **address**     | **string** |             | [default to undefined]            |
| **description** | **string** |             | [optional] [default to undefined] |

## Example

```typescript
import { AddressTranslationStoreRequest } from "./api";

const instance: AddressTranslationStoreRequest = {
  address_id,
  language_id,
  address,
  description,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
