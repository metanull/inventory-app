# PartnerTranslationImageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerTranslationImageDestroy**](#partnertranslationimagedestroy) | **DELETE** /partner-translation-image/{partnerTranslationImage} | Remove the specified resource from storage|
|[**partnerTranslationImageIndex**](#partnertranslationimageindex) | **GET** /partner-translation-image | Display a listing of the resource|
|[**partnerTranslationImageShow**](#partnertranslationimageshow) | **GET** /partner-translation-image/{partnerTranslationImage} | Display the specified resource|
|[**partnerTranslationImageStore**](#partnertranslationimagestore) | **POST** /partner-translation-image | Store a newly created resource in storage|
|[**partnerTranslationImageUpdate**](#partnertranslationimageupdate) | **PATCH** /partner-translation-image/{partnerTranslationImage} | Update the specified resource in storage|
|[**partnerTranslationImageUpdate2**](#partnertranslationimageupdate2) | **PUT** /partner-translation-image/{partnerTranslationImage} | Update the specified resource in storage|

# **partnerTranslationImageDestroy**
> partnerTranslationImageDestroy()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageDestroy(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


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

# **partnerTranslationImageIndex**
> PartnerTranslationImageIndex200Response partnerTranslationImageIndex()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageIndex(
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

**PartnerTranslationImageIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;PartnerTranslationImageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationImageShow**
> PartnerTranslationImageShow200Response partnerTranslationImageShow()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageShow(
    partnerTranslationImage,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**PartnerTranslationImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationImageStore**
> PartnerTranslationImageShow200Response partnerTranslationImageStore(storePartnerTranslationImageRequest)


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration,
    StorePartnerTranslationImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let storePartnerTranslationImageRequest: StorePartnerTranslationImageRequest; //

const { status, data } = await apiInstance.partnerTranslationImageStore(
    storePartnerTranslationImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storePartnerTranslationImageRequest** | **StorePartnerTranslationImageRequest**|  | |


### Return type

**PartnerTranslationImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationImageResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationImageUpdate**
> PartnerTranslationImageShow200Response partnerTranslationImageUpdate(updatePartnerTranslationImageRequest)


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration,
    UpdatePartnerTranslationImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)
let updatePartnerTranslationImageRequest: UpdatePartnerTranslationImageRequest; //

const { status, data } = await apiInstance.partnerTranslationImageUpdate(
    partnerTranslationImage,
    updatePartnerTranslationImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerTranslationImageRequest** | **UpdatePartnerTranslationImageRequest**|  | |
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


### Return type

**PartnerTranslationImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationImageUpdate2**
> PartnerTranslationImageShow200Response partnerTranslationImageUpdate2(updatePartnerTranslationImageRequest)


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration,
    UpdatePartnerTranslationImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)
let updatePartnerTranslationImageRequest: UpdatePartnerTranslationImageRequest; //

const { status, data } = await apiInstance.partnerTranslationImageUpdate2(
    partnerTranslationImage,
    updatePartnerTranslationImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerTranslationImageRequest** | **UpdatePartnerTranslationImageRequest**|  | |
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


### Return type

**PartnerTranslationImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerTranslationImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

