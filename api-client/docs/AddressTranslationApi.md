# AddressTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**addressTranslationDestroy**](#addresstranslationdestroy) | **DELETE** /address-translation/{addressTranslation} | Remove the specified resource from storage|
|[**addressTranslationIndex**](#addresstranslationindex) | **GET** /address-translation | Display a listing of the resource|
|[**addressTranslationShow**](#addresstranslationshow) | **GET** /address-translation/{addressTranslation} | Display the specified resource|
|[**addressTranslationStore**](#addresstranslationstore) | **POST** /address-translation | Store a newly created resource in storage|
|[**addressTranslationUpdate**](#addresstranslationupdate) | **PUT** /address-translation/{addressTranslation} | Update the specified resource in storage|

# **addressTranslationDestroy**
> addressTranslationDestroy()


### Example

```typescript
import {
    AddressTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressTranslationApi(configuration);

let addressTranslation: string; //The address translation ID (default to undefined)

const { status, data } = await apiInstance.addressTranslationDestroy(
    addressTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **addressTranslation** | [**string**] | The address translation ID | defaults to undefined|


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

# **addressTranslationIndex**
> AddressTranslationIndex200Response addressTranslationIndex()


### Example

```typescript
import {
    AddressTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressTranslationApi(configuration);

const { status, data } = await apiInstance.addressTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**AddressTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;AddressTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressTranslationShow**
> AddressTranslationStore200Response addressTranslationShow()


### Example

```typescript
import {
    AddressTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressTranslationApi(configuration);

let addressTranslation: string; //The address translation ID (default to undefined)

const { status, data } = await apiInstance.addressTranslationShow(
    addressTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **addressTranslation** | [**string**] | The address translation ID | defaults to undefined|


### Return type

**AddressTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AddressTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressTranslationStore**
> AddressTranslationStore200Response addressTranslationStore(addressTranslationStoreRequest)


### Example

```typescript
import {
    AddressTranslationApi,
    Configuration,
    AddressTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressTranslationApi(configuration);

let addressTranslationStoreRequest: AddressTranslationStoreRequest; //

const { status, data } = await apiInstance.addressTranslationStore(
    addressTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **addressTranslationStoreRequest** | **AddressTranslationStoreRequest**|  | |


### Return type

**AddressTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AddressTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressTranslationUpdate**
> AddressTranslationStore200Response addressTranslationUpdate()


### Example

```typescript
import {
    AddressTranslationApi,
    Configuration,
    AddressTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressTranslationApi(configuration);

let addressTranslation: string; //The address translation ID (default to undefined)
let addressTranslationUpdateRequest: AddressTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.addressTranslationUpdate(
    addressTranslation,
    addressTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **addressTranslationUpdateRequest** | **AddressTranslationUpdateRequest**|  | |
| **addressTranslation** | [**string**] | The address translation ID | defaults to undefined|


### Return type

**AddressTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AddressTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

