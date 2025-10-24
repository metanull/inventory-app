# PartnerTranslationImageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerTranslationAttachImage**](#partnertranslationattachimage) | **POST** /partner-translation/{partnerTranslation}/attach-image | Attach an available image to a partner translation|
|[**partnerTranslationImageDestroy**](#partnertranslationimagedestroy) | **DELETE** /partner-translation-image/{partnerTranslationImage} | Remove the specified resource from storage|
|[**partnerTranslationImageDetach**](#partnertranslationimagedetach) | **POST** /partner-translation-image/{partnerTranslationImage}/detach | Detach a partner translation image and convert it back to available image|
|[**partnerTranslationImageDownload**](#partnertranslationimagedownload) | **GET** /partner-translation-image/{partnerTranslationImage}/download | Returns the file to the caller|
|[**partnerTranslationImageIndex**](#partnertranslationimageindex) | **GET** /partner-translation-image | Display a listing of the resource|
|[**partnerTranslationImageMoveDown**](#partnertranslationimagemovedown) | **PATCH** /partner-translation-image/{partnerTranslationImage}/move-down | Move partner translation image down in display order|
|[**partnerTranslationImageMoveUp**](#partnertranslationimagemoveup) | **PATCH** /partner-translation-image/{partnerTranslationImage}/move-up | Move partner translation image up in display order|
|[**partnerTranslationImageShow**](#partnertranslationimageshow) | **GET** /partner-translation-image/{partnerTranslationImage} | Display the specified resource|
|[**partnerTranslationImageStore**](#partnertranslationimagestore) | **POST** /partner-translation-image | Store a newly created resource in storage|
|[**partnerTranslationImageTightenOrdering**](#partnertranslationimagetightenordering) | **PATCH** /partner-translation-image/{partnerTranslationImage}/tighten-ordering | Tighten ordering for all images of the partner translation|
|[**partnerTranslationImageUpdate**](#partnertranslationimageupdate) | **PATCH** /partner-translation-image/{partnerTranslationImage} | Update the specified resource in storage|
|[**partnerTranslationImageUpdate2**](#partnertranslationimageupdate2) | **PUT** /partner-translation-image/{partnerTranslationImage} | Update the specified resource in storage|
|[**partnerTranslationImageView**](#partnertranslationimageview) | **GET** /partner-translation-image/{partnerTranslationImage}/view | Returns the image file for direct viewing (e.g., for use in &lt;img&gt; src attribute)|

# **partnerTranslationAttachImage**
> PartnerTranslationImageShow200Response partnerTranslationAttachImage(attachFromAvailablePartnerTranslationImageRequest)


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration,
    AttachFromAvailablePartnerTranslationImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslation: string; //The partner translation ID (default to undefined)
let attachFromAvailablePartnerTranslationImageRequest: AttachFromAvailablePartnerTranslationImageRequest; //

const { status, data } = await apiInstance.partnerTranslationAttachImage(
    partnerTranslation,
    attachFromAvailablePartnerTranslationImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachFromAvailablePartnerTranslationImageRequest** | **AttachFromAvailablePartnerTranslationImageRequest**|  | |
| **partnerTranslation** | [**string**] | The partner translation ID | defaults to undefined|


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

# **partnerTranslationImageDetach**
> CollectionImageTightenOrdering200Response partnerTranslationImageDetach()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageDetach(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


### Return type

**CollectionImageTightenOrdering200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;OperationSuccessResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationImageDownload**
> object partnerTranslationImageDownload()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageDownload(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


### Return type

**object**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
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

# **partnerTranslationImageMoveDown**
> PartnerTranslationImageShow200Response partnerTranslationImageMoveDown()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageMoveDown(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerTranslationImageMoveUp**
> PartnerTranslationImageShow200Response partnerTranslationImageMoveUp()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageMoveUp(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


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

# **partnerTranslationImageTightenOrdering**
> CollectionImageTightenOrdering200Response partnerTranslationImageTightenOrdering()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageTightenOrdering(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


### Return type

**CollectionImageTightenOrdering200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;OperationSuccessResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

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

# **partnerTranslationImageView**
> object partnerTranslationImageView()


### Example

```typescript
import {
    PartnerTranslationImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerTranslationImageApi(configuration);

let partnerTranslationImage: string; //The partner translation image ID (default to undefined)

const { status, data } = await apiInstance.partnerTranslationImageView(
    partnerTranslationImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerTranslationImage** | [**string**] | The partner translation image ID | defaults to undefined|


### Return type

**object**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

