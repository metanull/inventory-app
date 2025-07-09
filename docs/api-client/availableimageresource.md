---
layout: default
title: "AvailableImageResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# AvailableImageResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the picture (GUID) | [default to undefined]
**path** | **string** | The path to the picture file | [default to undefined]
**comment** | **string** | A user defined comment associated with the file | [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { AvailableImageResource } from './api';

const instance: AvailableImageResource = {
    id,
    path,
    comment,
    created_at,
    updated_at,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
