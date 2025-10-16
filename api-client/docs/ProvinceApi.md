# ProvinceApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**provinceDestroy**](#provincedestroy) | **DELETE** /province/{province} | Remove the specified province|
|[**provinceIndex**](#provinceindex) | **GET** /province | Display a listing of provinces|
|[**provinceShow**](#provinceshow) | **GET** /province/{province} | Display the specified province|
|[**provinceStore**](#provincestore) | **POST** /province | Store a newly created province|
|[**provinceUpdate**](#provinceupdate) | **PATCH** /province/{province} | Update the specified province|
|[**provinceUpdate2**](#provinceupdate2) | **PUT** /province/{province} | Update the specified province|

# **provinceDestroy**
> provinceDestroy()


### Example

```typescript
import {
    ProvinceApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)

const { status, data } = await apiInstance.provinceDestroy(
    province
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **province** | [**string**] | The province ID | defaults to undefined|


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

# **provinceIndex**
> ProvinceIndex200Response provinceIndex()


### Example

```typescript
import {
    ProvinceApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.provinceIndex(
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

**ProvinceIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ProvinceResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceShow**
> ProvinceShow200Response provinceShow()


### Example

```typescript
import {
    ProvinceApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.provinceShow(
    province,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **province** | [**string**] | The province ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ProvinceShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceStore**
> ProvinceShow200Response provinceStore(storeProvinceRequest)


### Example

```typescript
import {
    ProvinceApi,
    Configuration,
    StoreProvinceRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let storeProvinceRequest: StoreProvinceRequest; //

const { status, data } = await apiInstance.provinceStore(
    storeProvinceRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeProvinceRequest** | **StoreProvinceRequest**|  | |


### Return type

**ProvinceShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** | &#x60;ProvinceResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceUpdate**
> ProvinceShow200Response provinceUpdate(updateProvinceRequest)


### Example

```typescript
import {
    ProvinceApi,
    Configuration,
    UpdateProvinceRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)
let updateProvinceRequest: UpdateProvinceRequest; //

const { status, data } = await apiInstance.provinceUpdate(
    province,
    updateProvinceRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateProvinceRequest** | **UpdateProvinceRequest**|  | |
| **province** | [**string**] | The province ID | defaults to undefined|


### Return type

**ProvinceShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceUpdate2**
> ProvinceShow200Response provinceUpdate2(updateProvinceRequest)


### Example

```typescript
import {
    ProvinceApi,
    Configuration,
    UpdateProvinceRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceApi(configuration);

let province: string; //The province ID (default to undefined)
let updateProvinceRequest: UpdateProvinceRequest; //

const { status, data } = await apiInstance.provinceUpdate2(
    province,
    updateProvinceRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateProvinceRequest** | **UpdateProvinceRequest**|  | |
| **province** | [**string**] | The province ID | defaults to undefined|


### Return type

**ProvinceShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

