---
layout: default
title: "ContactResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# ContactResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**phone_number** | **string** |  | [default to undefined]
**formatted_phone_number** | **string** |  | [default to undefined]
**fax_number** | **string** |  | [default to undefined]
**formatted_fax_number** | **string** |  | [default to undefined]
**email** | **string** |  | [default to undefined]
**translations** | [**Array&lt;ContactTranslationResource&gt;**](ContactTranslationResource.md) |  | [optional] [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { ContactResource } from './api';

const instance: ContactResource = {
    id,
    internal_name,
    phone_number,
    formatted_phone_number,
    fax_number,
    formatted_fax_number,
    email,
    translations,
    created_at,
    updated_at,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
