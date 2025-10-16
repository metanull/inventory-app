# PartnerApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerDestroy**](#partnerdestroy) | **DELETE** /partner/{partner} | Remove the specified resource from storage|
|[**partnerIndex**](#partnerindex) | **GET** /partner | Display a listing of the resource|
|[**partnerShow**](#partnershow) | **GET** /partner/{partner} | Display the specified resource|
|[**partnerStore**](#partnerstore) | **POST** /partner | Store a newly created resource in storage|
|[**partnerUpdate**](#partnerupdate) | **PATCH** /partner/{partner} | Update the specified resource in storage|
|[**partnerUpdate2**](#partnerupdate2) | **PUT** /partner/{partner} | Update the specified resource in storage|

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

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerIndex(
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

**PartnerIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;PartnerResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerShow**
> PartnerShow200Response partnerShow()


### Example

```typescript
import {
    PartnerApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partner: string; //The partner ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerShow(
    partner,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partner** | [**string**] | The partner ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**PartnerShow200Response**

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
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerStore**
> PartnerShow200Response partnerStore(storePartnerRequest)


### Example

```typescript
import {
    PartnerApi,
    Configuration,
    StorePartnerRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let storePartnerRequest: StorePartnerRequest; //

const { status, data } = await apiInstance.partnerStore(
    storePartnerRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storePartnerRequest** | **StorePartnerRequest**|  | |


### Return type

**PartnerShow200Response**

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
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerUpdate**
> PartnerShow200Response partnerUpdate(updatePartnerRequest)


### Example

```typescript
import {
    PartnerApi,
    Configuration,
    UpdatePartnerRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partner: string; //The partner ID (default to undefined)
let updatePartnerRequest: UpdatePartnerRequest; //

const { status, data } = await apiInstance.partnerUpdate(
    partner,
    updatePartnerRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerRequest** | **UpdatePartnerRequest**|  | |
| **partner** | [**string**] | The partner ID | defaults to undefined|


### Return type

**PartnerShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerUpdate2**
> PartnerShow200Response partnerUpdate2(updatePartnerRequest)


### Example

```typescript
import {
    PartnerApi,
    Configuration,
    UpdatePartnerRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerApi(configuration);

let partner: string; //The partner ID (default to undefined)
let updatePartnerRequest: UpdatePartnerRequest; //

const { status, data } = await apiInstance.partnerUpdate2(
    partner,
    updatePartnerRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerRequest** | **UpdatePartnerRequest**|  | |
| **partner** | [**string**] | The partner ID | defaults to undefined|


### Return type

**PartnerShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

