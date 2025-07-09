---
layout: default
title: "ContactUpdateRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# ContactUpdateRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**internal_name** | **string** |  | [default to undefined]
**phone_number** | **string** |  | [optional] [default to undefined]
**fax_number** | **string** |  | [optional] [default to undefined]
**email** | **string** |  | [optional] [default to undefined]
**translations** | [**Array&lt;ContactUpdateRequestTranslationsInner&gt;**](ContactUpdateRequestTranslationsInner.md) |  | [optional] [default to undefined]

## Example

```typescript
import { ContactUpdateRequest } from './api';

const instance: ContactUpdateRequest = {
    internal_name,
    phone_number,
    fax_number,
    email,
    translations,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
