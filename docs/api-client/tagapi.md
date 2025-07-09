---
layout: default
title: "TagApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# TagApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**tagDestroy**](#tagdestroy) | **DELETE** /tag/{tag} | Remove the specified resource from storage|
|[**tagForItem**](#tagforitem) | **GET** /tag/for-item/{item} | Get tags for a specific item|
|[**tagIndex**](#tagindex) | **GET** /tag | Display a listing of the resource|
|[**tagShow**](#tagshow) | **GET** /tag/{tag} | Display the specified resource|
|[**tagStore**](#tagstore) | **POST** /tag | Store a newly created resource in storage|
|[**tagUpdate**](#tagupdate) | **PUT** /tag/{tag} | Update the specified resource in storage|

# **tagDestroy**
> tagDestroy()


### Example

```typescript
import {
    TagApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new TagApi(configuration);

let tag: string; //The tag ID (default to undefined)

const { status, data } = await apiInstance.tagDestroy(
    tag
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tag** | [**string**] | The tag ID | defaults to undefined|


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

# **tagForItem**
> TagIndex200Response tagForItem()


### Example

```typescript
import {
    TagApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new TagApi(configuration);

let item: string; //The item ID (default to undefined)

const { status, data } = await apiInstance.tagForItem(
    item
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **item** | [**string**] | The item ID | defaults to undefined|


### Return type

**TagIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;TagResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **tagIndex**
> TagIndex200Response tagIndex()


### Example

```typescript
import {
    TagApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new TagApi(configuration);

const { status, data } = await apiInstance.tagIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**TagIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;TagResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **tagShow**
> TagStore200Response tagShow()


### Example

```typescript
import {
    TagApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new TagApi(configuration);

let tag: string; //The tag ID (default to undefined)

const { status, data } = await apiInstance.tagShow(
    tag
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tag** | [**string**] | The tag ID | defaults to undefined|


### Return type

**TagStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;TagResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **tagStore**
> TagStore200Response tagStore(tagStoreRequest)


### Example

```typescript
import {
    TagApi,
    Configuration,
    TagStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new TagApi(configuration);

let tagStoreRequest: TagStoreRequest; //

const { status, data } = await apiInstance.tagStore(
    tagStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tagStoreRequest** | **TagStoreRequest**|  | |


### Return type

**TagStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;TagResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **tagUpdate**
> TagStore200Response tagUpdate(tagStoreRequest)


### Example

```typescript
import {
    TagApi,
    Configuration,
    TagStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new TagApi(configuration);

let tag: string; //The tag ID (default to undefined)
let tagStoreRequest: TagStoreRequest; //

const { status, data } = await apiInstance.tagUpdate(
    tag,
    tagStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tagStoreRequest** | **TagStoreRequest**|  | |
| **tag** | [**string**] | The tag ID | defaults to undefined|


### Return type

**TagStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;TagResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
