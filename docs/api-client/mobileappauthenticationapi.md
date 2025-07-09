---
layout: default
title: "MobileAppAuthenticationApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# MobileAppAuthenticationApi

All URIs are relative to _http://localhost:8000/api_

| Method                            | HTTP request                   | Description                               |
| --------------------------------- | ------------------------------ | ----------------------------------------- |
| [**tokenAcquire**](#tokenacquire) | **POST** /mobile/acquire-token | Acquire a token for the user              |
| [**tokenWipe**](#tokenwipe)       | **GET** /mobile/wipe           | Revoke all the token for the current user |

# **tokenAcquire**

> string tokenAcquire(tokenAcquireRequest)

### Example

```typescript
import {
  MobileAppAuthenticationApi,
  Configuration,
  TokenAcquireRequest,
} from "./api";

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let tokenAcquireRequest: TokenAcquireRequest; //

const { status, data } = await apiInstance.tokenAcquire(tokenAcquireRequest);
```

### Parameters

| Name                    | Type                    | Description | Notes |
| ----------------------- | ----------------------- | ----------- | ----- |
| **tokenAcquireRequest** | **TokenAcquireRequest** |             |       |

### Return type

**string**

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description      | Response headers |
| ----------- | ---------------- | ---------------- |
| **201**     |                  | -                |
| **422**     | Validation error | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **tokenWipe**

> tokenWipe()

### Example

```typescript
import { MobileAppAuthenticationApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

const { status, data } = await apiInstance.tokenWipe();
```

### Parameters

This endpoint does not have any parameters.

### Return type

void (empty response body)

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **204**     | No content      | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
