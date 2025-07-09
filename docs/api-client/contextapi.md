---
layout: default
title: "ContextApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# ContextApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**contextDestroy**](#contextdestroy) | **DELETE** /context/{context} | Remove the specified resource from storage|
|[**contextGetDefault**](#contextgetdefault) | **GET** /context/default | Get the default context|
|[**contextIndex**](#contextindex) | **GET** /context | Display a listing of the resource|
|[**contextSetDefault**](#contextsetdefault) | **PATCH** /context/{context}/default | Set a context as the default one|
|[**contextShow**](#contextshow) | **GET** /context/{context} | Display the specified resource|
|[**contextStore**](#contextstore) | **POST** /context | Store a newly created resource in storage|
|[**contextUpdate**](#contextupdate) | **PUT** /context/{context} | Update the specified resource in storage|

# **contextDestroy**
> contextDestroy()


### Example

```typescript
import {
    ContextApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)

const { status, data } = await apiInstance.contextDestroy(
    context
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **context** | [**string**] | The context ID | defaults to undefined|


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

# **contextGetDefault**
> ContextSetDefault200Response contextGetDefault()


### Example

```typescript
import {
    ContextApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

const { status, data } = await apiInstance.contextGetDefault();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ContextSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**404** |  |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contextIndex**
> ContextIndex200Response contextIndex()


### Example

```typescript
import {
    ContextApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

const { status, data } = await apiInstance.contextIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ContextIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ContextResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contextSetDefault**
> ContextSetDefault200Response contextSetDefault(contextSetDefaultRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    ContextSetDefaultRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)
let contextSetDefaultRequest: ContextSetDefaultRequest; //

const { status, data } = await apiInstance.contextSetDefault(
    context,
    contextSetDefaultRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contextSetDefaultRequest** | **ContextSetDefaultRequest**|  | |
| **context** | [**string**] | The context ID | defaults to undefined|


### Return type

**ContextSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contextShow**
> ContextSetDefault200Response contextShow()


### Example

```typescript
import {
    ContextApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)

const { status, data } = await apiInstance.contextShow(
    context
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **context** | [**string**] | The context ID | defaults to undefined|


### Return type

**ContextSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contextStore**
> ContextSetDefault200Response contextStore(contextStoreRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    ContextStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let contextStoreRequest: ContextStoreRequest; //

const { status, data } = await apiInstance.contextStore(
    contextStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contextStoreRequest** | **ContextStoreRequest**|  | |


### Return type

**ContextSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contextUpdate**
> ContextSetDefault200Response contextUpdate(contextStoreRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    ContextStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)
let contextStoreRequest: ContextStoreRequest; //

const { status, data } = await apiInstance.contextUpdate(
    context,
    contextStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contextStoreRequest** | **ContextStoreRequest**|  | |
| **context** | [**string**] | The context ID | defaults to undefined|


### Return type

**ContextSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
