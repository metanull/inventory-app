---
layout: default
title: "CollectionApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# CollectionApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**collectionDestroy**](#collectiondestroy) | **DELETE** /collection/{collection} | Remove the specified collection from storage|
|[**collectionIndex**](#collectionindex) | **GET** /collection | Display a listing of the collections|
|[**collectionShow**](#collectionshow) | **GET** /collection/{collection} | Display the specified collection|
|[**collectionStore**](#collectionstore) | **POST** /collection | Store a newly created collection in storage|
|[**collectionUpdate**](#collectionupdate) | **PUT** /collection/{collection} | Update the specified collection in storage|

# **collectionDestroy**
> collectionDestroy()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)

const { status, data } = await apiInstance.collectionDestroy(
    collection
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|


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

# **collectionIndex**
> CollectionIndex200Response collectionIndex()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

const { status, data } = await apiInstance.collectionIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**CollectionIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;CollectionResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **collectionShow**
> CollectionStore200Response collectionShow()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)

const { status, data } = await apiInstance.collectionShow(
    collection
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **collectionStore**
> CollectionStore200Response collectionStore(collectionStoreRequest)


### Example

```typescript
import {
    CollectionApi,
    Configuration,
    CollectionStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collectionStoreRequest: CollectionStoreRequest; //

const { status, data } = await apiInstance.collectionStore(
    collectionStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionStoreRequest** | **CollectionStoreRequest**|  | |


### Return type

**CollectionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **collectionUpdate**
> CollectionStore200Response collectionUpdate()


### Example

```typescript
import {
    CollectionApi,
    Configuration,
    CollectionUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let collectionUpdateRequest: CollectionUpdateRequest; // (optional)

const { status, data } = await apiInstance.collectionUpdate(
    collection,
    collectionUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionUpdateRequest** | **CollectionUpdateRequest**|  | |
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
