---
layout: default
title: "CountryApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# CountryApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**countryDestroy**](#countrydestroy) | **DELETE** /country/{country} | Remove the specified resource from storage|
|[**countryIndex**](#countryindex) | **GET** /country | Display a listing of the resource|
|[**countryShow**](#countryshow) | **GET** /country/{country} | Display the specified resource|
|[**countryStore**](#countrystore) | **POST** /country | Store a newly created resource in storage|
|[**countryUpdate**](#countryupdate) | **PUT** /country/{country} | Update the specified resource in storage|

# **countryDestroy**
> countryDestroy()


### Example

```typescript
import {
    CountryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)

const { status, data } = await apiInstance.countryDestroy(
    country
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **country** | [**string**] | The country ID | defaults to undefined|


### Return type

void (empty response body)

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**204** | No content |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **countryIndex**
> CountryIndex200Response countryIndex()


### Example

```typescript
import {
    CountryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

const { status, data } = await apiInstance.countryIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**CountryIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;CountryResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **countryShow**
> CountryStore200Response countryShow()


### Example

```typescript
import {
    CountryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)

const { status, data } = await apiInstance.countryShow(
    country
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **country** | [**string**] | The country ID | defaults to undefined|


### Return type

**CountryStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **countryStore**
> CountryStore200Response countryStore(countryStoreRequest)


### Example

```typescript
import {
    CountryApi,
    Configuration,
    CountryStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let countryStoreRequest: CountryStoreRequest; //

const { status, data } = await apiInstance.countryStore(
    countryStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **countryStoreRequest** | **CountryStoreRequest**|  | |


### Return type

**CountryStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **countryUpdate**
> CountryStore200Response countryUpdate(countryUpdateRequest)


### Example

```typescript
import {
    CountryApi,
    Configuration,
    CountryUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)
let countryUpdateRequest: CountryUpdateRequest; //

const { status, data } = await apiInstance.countryUpdate(
    country,
    countryUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **countryUpdateRequest** | **CountryUpdateRequest**|  | |
| **country** | [**string**] | The country ID | defaults to undefined|


### Return type

**CountryStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
