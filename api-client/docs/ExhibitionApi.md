# ExhibitionApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**exhibitionDestroy**](#exhibitiondestroy) | **DELETE** /exhibition/{exhibition} | Remove the specified exhibition from storage|
|[**exhibitionIndex**](#exhibitionindex) | **GET** /exhibition | Display a listing of the exhibitions|
|[**exhibitionShow**](#exhibitionshow) | **GET** /exhibition/{exhibition} | Display the specified exhibition|
|[**exhibitionStore**](#exhibitionstore) | **POST** /exhibition | Store a newly created exhibition in storage|
|[**exhibitionUpdate**](#exhibitionupdate) | **PUT** /exhibition/{exhibition} | Update the specified exhibition in storage|

# **exhibitionDestroy**
> exhibitionDestroy()


### Example

```typescript
import {
    ExhibitionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionApi(configuration);

let exhibition: string; //The exhibition ID (default to undefined)

const { status, data } = await apiInstance.exhibitionDestroy(
    exhibition
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibition** | [**string**] | The exhibition ID | defaults to undefined|


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

# **exhibitionIndex**
> ExhibitionIndex200Response exhibitionIndex()


### Example

```typescript
import {
    ExhibitionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.exhibitionIndex(
    page,
    perPage,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **page** | [**number**] |  | (optional) defaults to undefined|
| **perPage** | [**number**] |  | (optional) defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ExhibitionIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ExhibitionResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **exhibitionShow**
> ExhibitionStore200Response exhibitionShow()


### Example

```typescript
import {
    ExhibitionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionApi(configuration);

let exhibition: string; //The exhibition ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.exhibitionShow(
    exhibition,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibition** | [**string**] | The exhibition ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ExhibitionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ExhibitionResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **exhibitionStore**
> ExhibitionStore200Response exhibitionStore(exhibitionStoreRequest)


### Example

```typescript
import {
    ExhibitionApi,
    Configuration,
    ExhibitionStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionApi(configuration);

let exhibitionStoreRequest: ExhibitionStoreRequest; //

const { status, data } = await apiInstance.exhibitionStore(
    exhibitionStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionStoreRequest** | **ExhibitionStoreRequest**|  | |


### Return type

**ExhibitionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ExhibitionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **exhibitionUpdate**
> ExhibitionStore200Response exhibitionUpdate()


### Example

```typescript
import {
    ExhibitionApi,
    Configuration,
    ExhibitionUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionApi(configuration);

let exhibition: string; //The exhibition ID (default to undefined)
let exhibitionUpdateRequest: ExhibitionUpdateRequest; // (optional)

const { status, data } = await apiInstance.exhibitionUpdate(
    exhibition,
    exhibitionUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionUpdateRequest** | **ExhibitionUpdateRequest**|  | |
| **exhibition** | [**string**] | The exhibition ID | defaults to undefined|


### Return type

**ExhibitionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ExhibitionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

