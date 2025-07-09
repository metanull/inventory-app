---
layout: default
title: "TokenAcquireRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# TokenAcquireRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**email** | **string** |  | [default to undefined]
**password** | **string** |  | [default to undefined]
**device_name** | **string** |  | [default to undefined]
**wipe_tokens** | **boolean** |  | [optional] [default to undefined]

## Example

```typescript
import { TokenAcquireRequest } from './api';

const instance: TokenAcquireRequest = {
    email,
    password,
    device_name,
    wipe_tokens,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
