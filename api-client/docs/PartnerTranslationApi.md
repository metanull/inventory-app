# PartnerTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerTranslationDestroy**](#partnertranslationdestroy) | **DELETE** /partner-translation/{partnerTranslation} | Remove the specified resource from storage|
|[**partnerTranslationIndex**](#partnertranslationindex) | **GET** /partner-translation | Display a listing of the resource|
|[**partnerTranslationShow**](#partnertranslationshow) | **GET** /partner-translation/{partnerTranslation} | Display the specified resource|
|[**partnerTranslationStore**](#partnertranslationstore) | **POST** /partner-translation | Store a newly created resource in storage|
|[**partnerTranslationUpdate**](#partnertranslationupdate) | **PATCH** /partner-translation/{partnerTranslation} | Update the specified resource in storage|
|[**partnerTranslationUpdate2**](#partnertranslationupdate2) | **PUT** /partner-translation/{partnerTranslation} | Update the specified resource in storage|

# **partnerTranslationDestroy**
> partnerTranslationDestroy()


### Example

```typescript
import {
    PartnerTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationApi(configuration);

let partnerTranslation: string; //The partner translation ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationDestroy(
    partnerTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslation** | [**string**] | The partner translation ID | defaults to undefined|


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

# **partnerTranslationIndex**
> PartnerTranslationIndex200Response partnerTranslationIndex()


### Example

```typescript
import {
    PartnerTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `partner`, `language`, `context`, `partnerTranslationImages`. (optional) (default to undefined)

const { status, data } = await apiInstance.partnerTranslationIndex(
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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;, &#x60;language&#x60;, &#x60;context&#x60;, &#x60;partnerTranslationImages&#x60;. | (optional) defaults to undefined|


### Return type

**PartnerTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;PartnerTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationShow**
> PartnerTranslationShow200Response partnerTranslationShow()


### Example

```typescript
import {
    PartnerTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationApi(configuration);

let partnerTranslation: string; //The partner translation ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `partner`, `language`, `context`, `partnerTranslationImages`. (optional) (default to undefined)

const { status, data } = await apiInstance.partnerTranslationShow(
    partnerTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslation** | [**string**] | The partner translation ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;, &#x60;language&#x60;, &#x60;context&#x60;, &#x60;partnerTranslationImages&#x60;. | (optional) defaults to undefined|


### Return type

**PartnerTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationStore**
> PartnerTranslationShow200Response partnerTranslationStore(storePartnerTranslationRequest)


### Example

```typescript
import {
    PartnerTranslationApi,
    Configuration,
    StorePartnerTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationApi(configuration);

let storePartnerTranslationRequest: StorePartnerTranslationRequest; //

const { status, data } = await apiInstance.partnerTranslationStore(
    storePartnerTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storePartnerTranslationRequest** | **StorePartnerTranslationRequest**|  | |


### Return type

**PartnerTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationUpdate**
> PartnerTranslationShow200Response partnerTranslationUpdate(updatePartnerTranslationRequest)


### Example

```typescript
import {
    PartnerTranslationApi,
    Configuration,
    UpdatePartnerTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationApi(configuration);

let partnerTranslation: string; //The partner translation ID (default to undefined)
let updatePartnerTranslationRequest: UpdatePartnerTranslationRequest; //

const { status, data } = await apiInstance.partnerTranslationUpdate(
    partnerTranslation,
    updatePartnerTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerTranslationRequest** | **UpdatePartnerTranslationRequest**|  | |
| **partnerTranslation** | [**string**] | The partner translation ID | defaults to undefined|


### Return type

**PartnerTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationUpdate2**
> PartnerTranslationShow200Response partnerTranslationUpdate2(updatePartnerTranslationRequest)


### Example

```typescript
import {
    PartnerTranslationApi,
    Configuration,
    UpdatePartnerTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationApi(configuration);

let partnerTranslation: string; //The partner translation ID (default to undefined)
let updatePartnerTranslationRequest: UpdatePartnerTranslationRequest; //

const { status, data } = await apiInstance.partnerTranslationUpdate2(
    partnerTranslation,
    updatePartnerTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerTranslationRequest** | **UpdatePartnerTranslationRequest**|  | |
| **partnerTranslation** | [**string**] | The partner translation ID | defaults to undefined|


### Return type

**PartnerTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

