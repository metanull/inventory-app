# ProvinceTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**provinceTranslationDestroy**](#provincetranslationdestroy) | **DELETE** /province-translation/{provinceTranslation} | Remove the specified resource from storage|
|[**provinceTranslationIndex**](#provincetranslationindex) | **GET** /province-translation | Display a listing of the resource|
|[**provinceTranslationShow**](#provincetranslationshow) | **GET** /province-translation/{provinceTranslation} | Display the specified resource|
|[**provinceTranslationStore**](#provincetranslationstore) | **POST** /province-translation | Store a newly created resource in storage|
|[**provinceTranslationUpdate**](#provincetranslationupdate) | **PATCH** /province-translation/{provinceTranslation} | Update the specified resource in storage|
|[**provinceTranslationUpdate2**](#provincetranslationupdate2) | **PUT** /province-translation/{provinceTranslation} | Update the specified resource in storage|

# **provinceTranslationDestroy**
> provinceTranslationDestroy()


### Example

```typescript
import {
    ProvinceTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)

const { status, data } = await apiInstance.provinceTranslationDestroy(
    provinceTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **provinceTranslation** | [**string**] | The province translation ID | defaults to undefined|


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

# **provinceTranslationIndex**
> ProvinceTranslationIndex200Response provinceTranslationIndex()


### Example

```typescript
import {
    ProvinceTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

const { status, data } = await apiInstance.provinceTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ProvinceTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ProvinceTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceTranslationShow**
> ProvinceTranslationShow200Response provinceTranslationShow()


### Example

```typescript
import {
    ProvinceTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)

const { status, data } = await apiInstance.provinceTranslationShow(
    provinceTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **provinceTranslation** | [**string**] | The province translation ID | defaults to undefined|


### Return type

**ProvinceTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceTranslationStore**
> ProvinceTranslationShow200Response provinceTranslationStore(storeProvinceTranslationRequest)


### Example

```typescript
import {
    ProvinceTranslationApi,
    Configuration,
    StoreProvinceTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let storeProvinceTranslationRequest: StoreProvinceTranslationRequest; //

const { status, data } = await apiInstance.provinceTranslationStore(
    storeProvinceTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeProvinceTranslationRequest** | **StoreProvinceTranslationRequest**|  | |


### Return type

**ProvinceTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceTranslationUpdate**
> ProvinceTranslationShow200Response provinceTranslationUpdate()


### Example

```typescript
import {
    ProvinceTranslationApi,
    Configuration,
    UpdateProvinceTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)
let updateProvinceTranslationRequest: UpdateProvinceTranslationRequest; // (optional)

const { status, data } = await apiInstance.provinceTranslationUpdate(
    provinceTranslation,
    updateProvinceTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateProvinceTranslationRequest** | **UpdateProvinceTranslationRequest**|  | |
| **provinceTranslation** | [**string**] | The province translation ID | defaults to undefined|


### Return type

**ProvinceTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **provinceTranslationUpdate2**
> ProvinceTranslationShow200Response provinceTranslationUpdate2()


### Example

```typescript
import {
    ProvinceTranslationApi,
    Configuration,
    UpdateProvinceTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)
let updateProvinceTranslationRequest: UpdateProvinceTranslationRequest; // (optional)

const { status, data } = await apiInstance.provinceTranslationUpdate2(
    provinceTranslation,
    updateProvinceTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateProvinceTranslationRequest** | **UpdateProvinceTranslationRequest**|  | |
| **provinceTranslation** | [**string**] | The province translation ID | defaults to undefined|


### Return type

**ProvinceTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProvinceTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

