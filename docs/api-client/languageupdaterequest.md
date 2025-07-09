---
layout: default
title: "LanguageUpdateRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# LanguageUpdateRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**internal_name** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [optional] [default to undefined]
**is_default** | **boolean** |  | [optional] [default to undefined]

## Example

```typescript
import { LanguageUpdateRequest } from './api';

const instance: LanguageUpdateRequest = {
    internal_name,
    backward_compatibility,
    is_default,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
