---
layout: default
title: "ProvinceResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# ProvinceResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**country_id** | **string** |  | [default to undefined]
**translations** | [**Array&lt;ProvinceTranslationResource&gt;**](ProvinceTranslationResource.md) |  | [optional] [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]

## Example

```typescript
import { ProvinceResource } from './api';

const instance: ProvinceResource = {
    id,
    internal_name,
    country_id,
    translations,
    created_at,
    updated_at,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
