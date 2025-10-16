# AddressApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**addressDestroy**](#addressdestroy) | **DELETE** /address/{address} | Remove the specified address|
|[**addressIndex**](#addressindex) | **GET** /address | Display a listing of addresses|
|[**addressShow**](#addressshow) | **GET** /address/{address} | Display the specified address|
|[**addressStore**](#addressstore) | **POST** /address | Store a newly created address|
|[**addressUpdate**](#addressupdate) | **PATCH** /address/{address} | Update the specified address|
|[**addressUpdate2**](#addressupdate2) | **PUT** /address/{address} | Update the specified address|

# **addressDestroy**
> addressDestroy()


### Example

```typescript
import {
    AddressApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)

const { status, data } = await apiInstance.addressDestroy(
    address
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **address** | [**string**] | The address ID | defaults to undefined|


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

# **addressIndex**
> AddressIndex200Response addressIndex()


### Example

```typescript
import {
    AddressApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.addressIndex(
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

**AddressIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;AddressResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressShow**
> AddressShow200Response addressShow()


### Example

```typescript
import {
    AddressApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.addressShow(
    address,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **address** | [**string**] | The address ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**AddressShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AddressResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressStore**
> AddressShow200Response addressStore(storeAddressRequest)


### Example

```typescript
import {
    AddressApi,
    Configuration,
    StoreAddressRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let storeAddressRequest: StoreAddressRequest; //

const { status, data } = await apiInstance.addressStore(
    storeAddressRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeAddressRequest** | **StoreAddressRequest**|  | |


### Return type

**AddressShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** | &#x60;AddressResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressUpdate**
> AddressShow200Response addressUpdate(updateAddressRequest)


### Example

```typescript
import {
    AddressApi,
    Configuration,
    UpdateAddressRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)
let updateAddressRequest: UpdateAddressRequest; //

const { status, data } = await apiInstance.addressUpdate(
    address,
    updateAddressRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateAddressRequest** | **UpdateAddressRequest**|  | |
| **address** | [**string**] | The address ID | defaults to undefined|


### Return type

**AddressShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AddressResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **addressUpdate2**
> AddressShow200Response addressUpdate2(updateAddressRequest)


### Example

```typescript
import {
    AddressApi,
    Configuration,
    UpdateAddressRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new AddressApi(configuration);

let address: string; //The address ID (default to undefined)
let updateAddressRequest: UpdateAddressRequest; //

const { status, data } = await apiInstance.addressUpdate2(
    address,
    updateAddressRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateAddressRequest** | **UpdateAddressRequest**|  | |
| **address** | [**string**] | The address ID | defaults to undefined|


### Return type

**AddressShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AddressResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

