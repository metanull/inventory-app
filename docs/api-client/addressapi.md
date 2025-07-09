---
layout: default
title: "AddressApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# AddressApi

All URIs are relative to _http://localhost:8000/api_

| Method                                | HTTP request                  | Description                    |
| ------------------------------------- | ----------------------------- | ------------------------------ |
| [**addressDestroy**](#addressdestroy) | **DELETE** /address/{address} | Remove the specified address   |
| [**addressIndex**](#addressindex)     | **GET** /address              | Display a listing of addresses |
| [**addressShow**](#addressshow)       | **GET** /address/{address}    | Display the specified address  |
| [**addressStore**](#addressstore)     | **POST** /address             | Store a newly created address  |
| [**addressUpdate**](#addressupdate)   | **PUT** /address/{address}    | Update the specified address   |

# **addressDestroy**

> addressDestroy()

### Example

```typescript
import { AddressApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)

const { status, data } = await apiInstance.addressDestroy(address);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **address** | [**string**] | The address ID | defaults to undefined |

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

# **addressIndex**

> AddressIndex200Response addressIndex()

### Example

```typescript
import { AddressApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

const { status, data } = await apiInstance.addressIndex();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**AddressIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                          | Response headers |
| ----------- | ------------------------------------ | ---------------- |
| **200**     | Array of &#x60;AddressResource&#x60; | -                |
| **401**     | Unauthenticated                      | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **addressShow**

> AddressStore201Response addressShow()

### Example

```typescript
import { AddressApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)

const { status, data } = await apiInstance.addressShow(address);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **address** | [**string**] | The address ID | defaults to undefined |

### Return type

**AddressStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **200**     | &#x60;AddressResource&#x60; | -                |
| **404**     | Not found                   | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **addressStore**

> AddressStore201Response addressStore(addressStoreRequest)

### Example

```typescript
import { AddressApi, Configuration, AddressStoreRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let addressStoreRequest: AddressStoreRequest; //

const { status, data } = await apiInstance.addressStore(addressStoreRequest);
```

### Parameters

| Name                    | Type                    | Description | Notes |
| ----------------------- | ----------------------- | ----------- | ----- |
| **addressStoreRequest** | **AddressStoreRequest** |             |       |

### Return type

**AddressStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **201**     | &#x60;AddressResource&#x60; | -                |
| **422**     |                             | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **addressUpdate**

> AddressStore201Response addressUpdate(addressUpdateRequest)

### Example

```typescript
import { AddressApi, Configuration, AddressUpdateRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)
let addressUpdateRequest: AddressUpdateRequest; //

const { status, data } = await apiInstance.addressUpdate(
  address,
  addressUpdateRequest,
);
```

### Parameters

| Name                     | Type                     | Description    | Notes                 |
| ------------------------ | ------------------------ | -------------- | --------------------- |
| **addressUpdateRequest** | **AddressUpdateRequest** |                |                       |
| **address**              | [**string**]             | The address ID | defaults to undefined |

### Return type

**AddressStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **200**     | &#x60;AddressResource&#x60; | -                |
| **422**     |                             | -                |
| **404**     | Not found                   | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
