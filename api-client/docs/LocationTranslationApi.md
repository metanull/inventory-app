# LocationTranslationApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**locationTranslationDestroy**](#locationtranslationdestroy) | **DELETE** /location-translation/{locationTranslation} | Remove the specified resource from storage|
|[**locationTranslationIndex**](#locationtranslationindex) | **GET** /location-translation | Display a listing of the resource|
|[**locationTranslationShow**](#locationtranslationshow) | **GET** /location-translation/{locationTranslation} | Display the specified resource|
|[**locationTranslationStore**](#locationtranslationstore) | **POST** /location-translation | Store a newly created resource in storage|
|[**locationTranslationUpdate**](#locationtranslationupdate) | **PUT** /location-translation/{locationTranslation} | Update the specified resource in storage|

# **locationTranslationDestroy**
> locationTranslationDestroy()


### Example

```typescript
import {
    LocationTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationTranslationApi(configuration);

let locationTranslation: string; //The location translation ID (default to undefined)

const { status, data } = await apiInstance.locationTranslationDestroy(
    locationTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **locationTranslation** | [**string**] | The location translation ID | defaults to undefined|


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

# **locationTranslationIndex**
> LocationTranslationIndex200Response locationTranslationIndex()


### Example

```typescript
import {
    LocationTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationTranslationApi(configuration);

const { status, data } = await apiInstance.locationTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**LocationTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;LocationTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationTranslationShow**
> LocationTranslationStore200Response locationTranslationShow()


### Example

```typescript
import {
    LocationTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationTranslationApi(configuration);

let locationTranslation: string; //The location translation ID (default to undefined)

const { status, data } = await apiInstance.locationTranslationShow(
    locationTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **locationTranslation** | [**string**] | The location translation ID | defaults to undefined|


### Return type

**LocationTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationTranslationStore**
> LocationTranslationStore200Response locationTranslationStore(locationTranslationStoreRequest)


### Example

```typescript
import {
    LocationTranslationApi,
    Configuration,
    LocationTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationTranslationApi(configuration);

let locationTranslationStoreRequest: LocationTranslationStoreRequest; //

const { status, data } = await apiInstance.locationTranslationStore(
    locationTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **locationTranslationStoreRequest** | **LocationTranslationStoreRequest**|  | |


### Return type

**LocationTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **locationTranslationUpdate**
> LocationTranslationStore200Response locationTranslationUpdate()


### Example

```typescript
import {
    LocationTranslationApi,
    Configuration,
    LocationTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LocationTranslationApi(configuration);

let locationTranslation: string; //The location translation ID (default to undefined)
let locationTranslationUpdateRequest: LocationTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.locationTranslationUpdate(
    locationTranslation,
    locationTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **locationTranslationUpdateRequest** | **LocationTranslationUpdateRequest**|  | |
| **locationTranslation** | [**string**] | The location translation ID | defaults to undefined|


### Return type

**LocationTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LocationTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

