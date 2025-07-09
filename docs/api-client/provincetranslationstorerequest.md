---
layout: default
title: "ProvinceTranslationStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# ProvinceTranslationStoreRequest

## Properties

| Name            | Type       | Description | Notes                             |
| --------------- | ---------- | ----------- | --------------------------------- |
| **province_id** | **string** |             | [default to undefined]            |
| **language_id** | **string** |             | [default to undefined]            |
| **name**        | **string** |             | [default to undefined]            |
| **description** | **string** |             | [optional] [default to undefined] |

## Example

```typescript
import { ProvinceTranslationStoreRequest } from "./api";

const instance: ProvinceTranslationStoreRequest = {
  province_id,
  language_id,
  name,
  description,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
