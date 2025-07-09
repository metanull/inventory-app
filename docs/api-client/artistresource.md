---
layout: default
title: "ArtistResource"
parent: TypeScript API Client
nav_order: 1
category: "Models"
---

# ArtistResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  | [default to undefined]
**name** | **string** |  | [default to undefined]
**place_of_birth** | **string** |  | [default to undefined]
**place_of_death** | **string** |  | [default to undefined]
**date_of_birth** | **string** |  | [default to undefined]
**date_of_death** | **string** |  | [default to undefined]
**period_of_activity** | **string** |  | [default to undefined]
**internal_name** | **string** |  | [default to undefined]
**backward_compatibility** | **string** |  | [default to undefined]
**created_at** | **string** |  | [default to undefined]
**updated_at** | **string** |  | [default to undefined]
**items** | [**Array&lt;ItemResource&gt;**](ItemResource.md) |  | [optional] [default to undefined]

## Example

```typescript
import { ArtistResource } from './api';

const instance: ArtistResource = {
    id,
    name,
    place_of_birth,
    place_of_death,
    date_of_birth,
    date_of_death,
    period_of_activity,
    internal_name,
    backward_compatibility,
    created_at,
    updated_at,
    items,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
