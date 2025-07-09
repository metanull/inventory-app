---
layout: default
title: "InlineObject"
parent: TypeScript API Client
nav_order: 1
category: "Other"
---

# InlineObject

## Properties

| Name        | Type                                        | Description                                                  | Notes                  |
| ----------- | ------------------------------------------- | ------------------------------------------------------------ | ---------------------- |
| **message** | **string**                                  | Errors overview.                                             | [default to undefined] |
| **errors**  | **{ [key: string]: Array&lt;string&gt;; }** | A detailed description of each field that failed validation. | [default to undefined] |

## Example

```typescript
import { InlineObject } from "./api";

const instance: InlineObject = {
  message,
  errors,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
