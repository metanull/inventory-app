# PartnerApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerDestroy**](#partnerdestroy) | **DELETE** /partner/{partner} | Remove the specified resource from storage|
|[**partnerIndex**](#partnerindex) | **GET** /partner | Display a listing of the resource|
|[**partnerShow**](#partnershow) | **GET** /partner/{partner} | Display the specified resource|
|[**partnerStore**](#partnerstore) | **POST** /partner | Store a newly created resource in storage|
|[**partnerUpdate**](#partnerupdate) | **PUT** /partner/{partner} | Update the specified resource in storage|

# **partnerDestroy**
> partnerDestroy()


### Example

```typescript
import {
    PartnerApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partner: string; //The partner ID (default to undefined)

const { status, data } = await apiInstance.partnerDestroy(
    partner
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partner** | [**string**] | The partner ID | defaults to undefined|


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

# **partnerIndex**
> PartnerIndex200Response partnerIndex()


### Example

```typescript
import {
    PartnerApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

const { status, data } = await apiInstance.partnerIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**PartnerIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;PartnerResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerShow**
> PartnerStore200Response partnerShow()


### Example

```typescript
import {
    PartnerApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partner: string; //The partner ID (default to undefined)

const { status, data } = await apiInstance.partnerShow(
    partner
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partner** | [**string**] | The partner ID | defaults to undefined|


### Return type

**PartnerStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerStore**
> PartnerStore200Response partnerStore(partnerStoreRequest)


### Example

```typescript
import {
    PartnerApi,
    Configuration,
    PartnerStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partnerStoreRequest: PartnerStoreRequest; //

const { status, data } = await apiInstance.partnerStore(
    partnerStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerStoreRequest** | **PartnerStoreRequest**|  | |


### Return type

**PartnerStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerUpdate**
> PartnerStore200Response partnerUpdate(partnerStoreRequest)


### Example

```typescript
import {
    PartnerApi,
    Configuration,
    PartnerStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partner: string; //The partner ID (default to undefined)
let partnerStoreRequest: PartnerStoreRequest; //

const { status, data } = await apiInstance.partnerUpdate(
    partner,
    partnerStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerStoreRequest** | **PartnerStoreRequest**|  | |
| **partner** | [**string**] | The partner ID | defaults to undefined|


### Return type

**PartnerStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

