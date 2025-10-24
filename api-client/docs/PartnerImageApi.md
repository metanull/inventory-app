# PartnerImageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerImageDestroy**](#partnerimagedestroy) | **DELETE** /partner-image/{partnerImage} | Remove the specified resource from storage|
|[**partnerImageIndex**](#partnerimageindex) | **GET** /partner-image | Display a listing of the resource|
|[**partnerImageShow**](#partnerimageshow) | **GET** /partner-image/{partnerImage} | Display the specified resource|
|[**partnerImageStore**](#partnerimagestore) | **POST** /partner-image | Store a newly created resource in storage|
|[**partnerImageUpdate**](#partnerimageupdate) | **PATCH** /partner-image/{partnerImage} | Update the specified resource in storage|
|[**partnerImageUpdate2**](#partnerimageupdate2) | **PUT** /partner-image/{partnerImage} | Update the specified resource in storage|

# **partnerImageDestroy**
> partnerImageDestroy()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageDestroy(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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

# **partnerImageIndex**
> PartnerImageIndex200Response partnerImageIndex()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerImageIndex(
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

**PartnerImageIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;PartnerImageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerImageShow**
> PartnerImageShow200Response partnerImageShow()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerImageShow(
    partnerImage,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**PartnerImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerImageStore**
> PartnerImageShow200Response partnerImageStore(storePartnerImageRequest)


### Example

```typescript
import {
    PartnerImageApi,
    Configuration,
    StorePartnerImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let storePartnerImageRequest: StorePartnerImageRequest; //

const { status, data } = await apiInstance.partnerImageStore(
    storePartnerImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storePartnerImageRequest** | **StorePartnerImageRequest**|  | |


### Return type

**PartnerImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerImageResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerImageUpdate**
> PartnerImageShow200Response partnerImageUpdate(updatePartnerImageRequest)


### Example

```typescript
import {
    PartnerImageApi,
    Configuration,
    UpdatePartnerImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)
let updatePartnerImageRequest: UpdatePartnerImageRequest; //

const { status, data } = await apiInstance.partnerImageUpdate(
    partnerImage,
    updatePartnerImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerImageRequest** | **UpdatePartnerImageRequest**|  | |
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


### Return type

**PartnerImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerImageUpdate2**
> PartnerImageShow200Response partnerImageUpdate2(updatePartnerImageRequest)


### Example

```typescript
import {
    PartnerImageApi,
    Configuration,
    UpdatePartnerImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)
let updatePartnerImageRequest: UpdatePartnerImageRequest; //

const { status, data } = await apiInstance.partnerImageUpdate2(
    partnerImage,
    updatePartnerImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerImageRequest** | **UpdatePartnerImageRequest**|  | |
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


### Return type

**PartnerImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

