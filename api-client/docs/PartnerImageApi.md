# PartnerImageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerAttachImage**](#partnerattachimage) | **POST** /partner/{partner}/attach-image | Attach an available image to a partner|
|[**partnerImageDestroy**](#partnerimagedestroy) | **DELETE** /partner-image/{partnerImage} | Remove the specified resource from storage|
|[**partnerImageDetach**](#partnerimagedetach) | **POST** /partner-image/{partnerImage}/detach | Detach a partner image and convert it back to available image|
|[**partnerImageDownload**](#partnerimagedownload) | **GET** /partner-image/{partnerImage}/download | Returns the file to the caller|
|[**partnerImageIndex**](#partnerimageindex) | **GET** /partner-image | Display a listing of the resource|
|[**partnerImageMoveDown**](#partnerimagemovedown) | **PATCH** /partner-image/{partnerImage}/move-down | Move partner image down in display order|
|[**partnerImageMoveUp**](#partnerimagemoveup) | **PATCH** /partner-image/{partnerImage}/move-up | Move partner image up in display order|
|[**partnerImageShow**](#partnerimageshow) | **GET** /partner-image/{partnerImage} | Display the specified resource|
|[**partnerImageStore**](#partnerimagestore) | **POST** /partner-image | Store a newly created resource in storage|
|[**partnerImageTightenOrdering**](#partnerimagetightenordering) | **PATCH** /partner-image/{partnerImage}/tighten-ordering | Tighten ordering for all images of the partner|
|[**partnerImageUpdate**](#partnerimageupdate) | **PATCH** /partner-image/{partnerImage} | Update the specified resource in storage|
|[**partnerImageUpdate2**](#partnerimageupdate2) | **PUT** /partner-image/{partnerImage} | Update the specified resource in storage|
|[**partnerImageView**](#partnerimageview) | **GET** /partner-image/{partnerImage}/view | Returns the image file for direct viewing (e.g., for use in &lt;img&gt; src attribute)|

# **partnerAttachImage**
> PartnerImageShow200Response partnerAttachImage(attachFromAvailablePartnerImageRequest)


### Example

```typescript
import {
    PartnerImageApi,
    Configuration,
    AttachFromAvailablePartnerImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partner: string; //The partner ID (default to undefined)
let attachFromAvailablePartnerImageRequest: AttachFromAvailablePartnerImageRequest; //

const { status, data } = await apiInstance.partnerAttachImage(
    partner,
    attachFromAvailablePartnerImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachFromAvailablePartnerImageRequest** | **AttachFromAvailablePartnerImageRequest**|  | |
| **partner** | [**string**] | The partner ID | defaults to undefined|


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

# **partnerImageDetach**
> CollectionImageTightenOrdering200Response partnerImageDetach()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageDetach(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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

# **partnerImageDownload**
> object partnerImageDownload()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageDownload(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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
let include: string; //Comma-separated list of related resources to include. Valid values: `partner`. (optional) (default to undefined)

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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;. | (optional) defaults to undefined|


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

# **partnerImageMoveDown**
> PartnerImageShow200Response partnerImageMoveDown()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageMoveDown(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerImageMoveUp**
> PartnerImageShow200Response partnerImageMoveUp()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageMoveUp(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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
let include: string; //Comma-separated list of related resources to include. Valid values: `partner`. (optional) (default to undefined)

const { status, data } = await apiInstance.partnerImageShow(
    partnerImage,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;. | (optional) defaults to undefined|


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

# **partnerImageTightenOrdering**
> CollectionImageTightenOrdering200Response partnerImageTightenOrdering()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageTightenOrdering(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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

# **partnerImageView**
> object partnerImageView()


### Example

```typescript
import {
    PartnerImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerImageApi(configuration);

let partnerImage: string; //The partner image ID (default to undefined)

const { status, data } = await apiInstance.partnerImageView(
    partnerImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerImage** | [**string**] | The partner image ID | defaults to undefined|


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

