# DetailApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**detailDestroy**](#detaildestroy) | **DELETE** /detail/{detail} | Remove the specified resource from storage|
|[**detailIndex**](#detailindex) | **GET** /detail | Display a listing of the resource|
|[**detailShow**](#detailshow) | **GET** /detail/{detail} | Display the specified resource|
|[**detailStore**](#detailstore) | **POST** /detail | Store a newly created resource in storage|
|[**detailUpdate**](#detailupdate) | **PUT** /detail/{detail} | Update the specified resource in storage|

# **detailDestroy**
> detailDestroy()


### Example

```typescript
import {
    DetailApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailApi(configuration);

let detail: string; //The detail ID (default to undefined)

const { status, data } = await apiInstance.detailDestroy(
    detail
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detail** | [**string**] | The detail ID | defaults to undefined|


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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **detailIndex**
> DetailIndex200Response detailIndex()


### Example

```typescript
import {
    DetailApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailApi(configuration);

const { status, data } = await apiInstance.detailIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**DetailIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;DetailResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **detailShow**
> DetailStore200Response detailShow()


### Example

```typescript
import {
    DetailApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailApi(configuration);

let detail: string; //The detail ID (default to undefined)

const { status, data } = await apiInstance.detailShow(
    detail
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detail** | [**string**] | The detail ID | defaults to undefined|


### Return type

**DetailStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;DetailResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **detailStore**
> DetailStore200Response detailStore(detailStoreRequest)


### Example

```typescript
import {
    DetailApi,
    Configuration,
    DetailStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailApi(configuration);

let detailStoreRequest: DetailStoreRequest; //

const { status, data } = await apiInstance.detailStore(
    detailStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detailStoreRequest** | **DetailStoreRequest**|  | |


### Return type

**DetailStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;DetailResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **detailUpdate**
> DetailStore200Response detailUpdate(detailStoreRequest)


### Example

```typescript
import {
    DetailApi,
    Configuration,
    DetailStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailApi(configuration);

let detail: string; //The detail ID (default to undefined)
let detailStoreRequest: DetailStoreRequest; //

const { status, data } = await apiInstance.detailUpdate(
    detail,
    detailStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detailStoreRequest** | **DetailStoreRequest**|  | |
| **detail** | [**string**] | The detail ID | defaults to undefined|


### Return type

**DetailStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;DetailResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

