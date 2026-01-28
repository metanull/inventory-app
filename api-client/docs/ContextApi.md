# ContextApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**contextClearDefault**](#contextcleardefault) | **DELETE** /context/default | Clear the default flag from any context|
|[**contextDestroy**](#contextdestroy) | **DELETE** /context/{context} | Remove the specified resource from storage|
|[**contextGetDefault**](#contextgetdefault) | **GET** /context/default | Get the default context|
|[**contextIndex**](#contextindex) | **GET** /context | Display a listing of the resource|
|[**contextSetDefault**](#contextsetdefault) | **PATCH** /context/{context}/default | Set or unset a context as the default one|
|[**contextShow**](#contextshow) | **GET** /context/{context} | Display the specified resource|
|[**contextStore**](#contextstore) | **POST** /context | Store a newly created resource in storage|
|[**contextUpdate**](#contextupdate) | **PATCH** /context/{context} | Update the specified resource in storage|
|[**contextUpdate2**](#contextupdate2) | **PUT** /context/{context} | Update the specified resource in storage|

# **contextClearDefault**
> ContextClearDefault200Response contextClearDefault()


### Example

```typescript
import {
    ContextApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

const { status, data } = await apiInstance.contextClearDefault();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ContextClearDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;MessageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contextGetDefault**
> ContextGetDefault200Response contextGetDefault()


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

**ContextGetDefault200Response**

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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

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

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //No related resources available for inclusion on this endpoint. (optional) (default to undefined)

const { status, data } = await apiInstance.contextIndex(
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
| **include** | [**string**] | No related resources available for inclusion on this endpoint. | (optional) defaults to undefined|


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
|**200** | Paginated set of &#x60;ContextResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contextSetDefault**
> ContextGetDefault200Response contextSetDefault(setDefaultContextRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    SetDefaultContextRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)
let setDefaultContextRequest: SetDefaultContextRequest; //

const { status, data } = await apiInstance.contextSetDefault(
    context,
    setDefaultContextRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **setDefaultContextRequest** | **SetDefaultContextRequest**|  | |
| **context** | [**string**] | The context ID | defaults to undefined|


### Return type

**ContextGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contextShow**
> ContextGetDefault200Response contextShow()


### Example

```typescript
import {
    ContextApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)
let include: string; //No related resources available for inclusion on this endpoint. (optional) (default to undefined)

const { status, data } = await apiInstance.contextShow(
    context,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **context** | [**string**] | The context ID | defaults to undefined|
| **include** | [**string**] | No related resources available for inclusion on this endpoint. | (optional) defaults to undefined|


### Return type

**ContextGetDefault200Response**

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
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contextStore**
> ContextGetDefault200Response contextStore(storeContextRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    StoreContextRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let storeContextRequest: StoreContextRequest; //

const { status, data } = await apiInstance.contextStore(
    storeContextRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeContextRequest** | **StoreContextRequest**|  | |


### Return type

**ContextGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contextUpdate**
> ContextGetDefault200Response contextUpdate(updateContextRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    UpdateContextRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)
let updateContextRequest: UpdateContextRequest; //

const { status, data } = await apiInstance.contextUpdate(
    context,
    updateContextRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateContextRequest** | **UpdateContextRequest**|  | |
| **context** | [**string**] | The context ID | defaults to undefined|


### Return type

**ContextGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contextUpdate2**
> ContextGetDefault200Response contextUpdate2(updateContextRequest)


### Example

```typescript
import {
    ContextApi,
    Configuration,
    UpdateContextRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContextApi(configuration);

let context: string; //The context ID (default to undefined)
let updateContextRequest: UpdateContextRequest; //

const { status, data } = await apiInstance.contextUpdate2(
    context,
    updateContextRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateContextRequest** | **UpdateContextRequest**|  | |
| **context** | [**string**] | The context ID | defaults to undefined|


### Return type

**ContextGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContextResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

