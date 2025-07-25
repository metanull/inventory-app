---
layout: default
title: "ProvinceApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# ProvinceApi

All URIs are relative to _http://localhost:8000/api_

| Method                                  | HTTP request                    | Description                    |
| --------------------------------------- | ------------------------------- | ------------------------------ |
| [**provinceDestroy**](#provincedestroy) | **DELETE** /province/{province} | Remove the specified province  |
| [**provinceIndex**](#provinceindex)     | **GET** /province               | Display a listing of provinces |
| [**provinceShow**](#provinceshow)       | **GET** /province/{province}    | Display the specified province |
| [**provinceStore**](#provincestore)     | **POST** /province              | Store a newly created province |
| [**provinceUpdate**](#provinceupdate)   | **PUT** /province/{province}    | Update the specified province  |

# **provinceDestroy**

> provinceDestroy()

### Example

```typescript
import { ProvinceApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)

const { status, data } = await apiInstance.provinceDestroy(province);
```

### Parameters

| Name         | Type         | Description     | Notes                 |
| ------------ | ------------ | --------------- | --------------------- |
| **province** | [**string**] | The province ID | defaults to undefined |

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
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceIndex**

> ProvinceIndex200Response provinceIndex()

### Example

```typescript
import { ProvinceApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

const { status, data } = await apiInstance.provinceIndex();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**ProvinceIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                           | Response headers |
| ----------- | ------------------------------------- | ---------------- |
| **200**     | Array of &#x60;ProvinceResource&#x60; | -                |
| **401**     | Unauthenticated                       | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceShow**

> ProvinceStore201Response provinceShow()

### Example

```typescript
import { ProvinceApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)

const { status, data } = await apiInstance.provinceShow(province);
```

### Parameters

| Name         | Type         | Description     | Notes                 |
| ------------ | ------------ | --------------- | --------------------- |
| **province** | [**string**] | The province ID | defaults to undefined |

### Return type

**ProvinceStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                  | Response headers |
| ----------- | ---------------------------- | ---------------- |
| **200**     | &#x60;ProvinceResource&#x60; | -                |
| **404**     | Not found                    | -                |
| **401**     | Unauthenticated              | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceStore**

> ProvinceStore201Response provinceStore(locationStoreRequest)

### Example

```typescript
import { ProvinceApi, Configuration, LocationStoreRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let locationStoreRequest: LocationStoreRequest; //

const { status, data } = await apiInstance.provinceStore(locationStoreRequest);
```

### Parameters

| Name                     | Type                     | Description | Notes |
| ------------------------ | ------------------------ | ----------- | ----- |
| **locationStoreRequest** | **LocationStoreRequest** |             |       |

### Return type

**ProvinceStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                  | Response headers |
| ----------- | ---------------------------- | ---------------- |
| **201**     | &#x60;ProvinceResource&#x60; | -                |
| **422**     |                              | -                |
| **401**     | Unauthenticated              | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceUpdate**

> ProvinceStore201Response provinceUpdate(locationUpdateRequest)

### Example

```typescript
import { ProvinceApi, Configuration, LocationUpdateRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)
let locationUpdateRequest: LocationUpdateRequest; //

const { status, data } = await apiInstance.provinceUpdate(
  province,
  locationUpdateRequest,
);
```

### Parameters

| Name                      | Type                      | Description     | Notes                 |
| ------------------------- | ------------------------- | --------------- | --------------------- |
| **locationUpdateRequest** | **LocationUpdateRequest** |                 |                       |
| **province**              | [**string**]              | The province ID | defaults to undefined |

### Return type

**ProvinceStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                  | Response headers |
| ----------- | ---------------------------- | ---------------- |
| **200**     | &#x60;ProvinceResource&#x60; | -                |
| **422**     |                              | -                |
| **404**     | Not found                    | -                |
| **401**     | Unauthenticated              | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
