# LocationApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**locationDestroy**](#locationdestroy) | **DELETE** /location/{location} | Remove the specified location|
|[**locationIndex**](#locationindex) | **GET** /location | Display a listing of locations|
|[**locationShow**](#locationshow) | **GET** /location/{location} | Display the specified location|
|[**locationStore**](#locationstore) | **POST** /location | Store a newly created location|
|[**locationUpdate**](#locationupdate) | **PUT** /location/{location} | Update the specified location|

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

const { status, data } = await apiInstance.locationIndex();
```

### Parameters
This endpoint does not have any parameters.


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
|**200** | Array of &#x60;LocationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationShow**
> LocationStore201Response locationShow()


### Example

```typescript
import {
    LocationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let location: string; //The location ID (default to undefined)

const { status, data } = await apiInstance.locationShow(
    location
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **location** | [**string**] | The location ID | defaults to undefined|


### Return type

**LocationStore201Response**

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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationStore**
> LocationStore201Response locationStore(locationStoreRequest)


### Example

```typescript
import {
    LocationApi,
    Configuration,
    LocationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let locationStoreRequest: LocationStoreRequest; //

const { status, data } = await apiInstance.locationStore(
    locationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **locationStoreRequest** | **LocationStoreRequest**|  | |


### Return type

**LocationStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** | &#x60;LocationResource&#x60; |  -  |
|**422** |  |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationUpdate**
> LocationStore201Response locationUpdate(locationUpdateRequest)


### Example

```typescript
import {
    LocationApi,
    Configuration,
    LocationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationApi(configuration);

let location: string; //The location ID (default to undefined)
let locationUpdateRequest: LocationUpdateRequest; //

const { status, data } = await apiInstance.locationUpdate(
    location,
    locationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **locationUpdateRequest** | **LocationUpdateRequest**|  | |
| **location** | [**string**] | The location ID | defaults to undefined|


### Return type

**LocationStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationResource&#x60; |  -  |
|**422** |  |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

