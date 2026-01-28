# LocationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**locationDestroy**](#locationdestroy) | **DELETE** /location/{location} | Remove the specified location|
|[**locationIndex**](#locationindex) | **GET** /location | Display a listing of locations|
|[**locationShow**](#locationshow) | **GET** /location/{location} | Display the specified location|
|[**locationStore**](#locationstore) | **POST** /location | Store a newly created location|
|[**locationUpdate**](#locationupdate) | **PATCH** /location/{location} | Update the specified location|
|[**locationUpdate2**](#locationupdate2) | **PUT** /location/{location} | Update the specified location|

# **locationDestroy**
> locationDestroy()


### Example

```typescript
import {
    LocationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let location: string; //The location ID (default to undefined)

const { status, data } = await apiInstance.locationDestroy(
    location
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **location** | [**string**] | The location ID | defaults to undefined|


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

# **locationIndex**
> LocationIndex200Response locationIndex()


### Example

```typescript
import {
    LocationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `translations`. (optional) (default to undefined)

const { status, data } = await apiInstance.locationIndex(
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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;translations&#x60;. | (optional) defaults to undefined|


### Return type

**LocationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;LocationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationShow**
> LocationShow200Response locationShow()


### Example

```typescript
import {
    LocationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let location: string; //The location ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `translations`. (optional) (default to undefined)

const { status, data } = await apiInstance.locationShow(
    location,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **location** | [**string**] | The location ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;translations&#x60;. | (optional) defaults to undefined|


### Return type

**LocationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationStore**
> LocationShow200Response locationStore(storeLocationRequest)


### Example

```typescript
import {
    LocationApi,
    Configuration,
    StoreLocationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let storeLocationRequest: StoreLocationRequest; //

const { status, data } = await apiInstance.locationStore(
    storeLocationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeLocationRequest** | **StoreLocationRequest**|  | |


### Return type

**LocationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** | &#x60;LocationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationUpdate**
> LocationShow200Response locationUpdate(updateLocationRequest)


### Example

```typescript
import {
    LocationApi,
    Configuration,
    UpdateLocationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let location: string; //The location ID (default to undefined)
let updateLocationRequest: UpdateLocationRequest; //

const { status, data } = await apiInstance.locationUpdate(
    location,
    updateLocationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateLocationRequest** | **UpdateLocationRequest**|  | |
| **location** | [**string**] | The location ID | defaults to undefined|


### Return type

**LocationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationUpdate2**
> LocationShow200Response locationUpdate2(updateLocationRequest)


### Example

```typescript
import {
    LocationApi,
    Configuration,
    UpdateLocationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let location: string; //The location ID (default to undefined)
let updateLocationRequest: UpdateLocationRequest; //

const { status, data } = await apiInstance.locationUpdate2(
    location,
    updateLocationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateLocationRequest** | **UpdateLocationRequest**|  | |
| **location** | [**string**] | The location ID | defaults to undefined|


### Return type

**LocationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

