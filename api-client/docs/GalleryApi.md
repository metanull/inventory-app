# GalleryApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**galleryDestroy**](#gallerydestroy) | **DELETE** /gallery/{gallery} | Remove the specified gallery from storage|
|[**galleryIndex**](#galleryindex) | **GET** /gallery | Display a listing of the galleries|
|[**galleryShow**](#galleryshow) | **GET** /gallery/{gallery} | Display the specified gallery|
|[**galleryStore**](#gallerystore) | **POST** /gallery | Store a newly created gallery in storage|
|[**galleryUpdate**](#galleryupdate) | **PUT** /gallery/{gallery} | Update the specified gallery in storage|

# **galleryDestroy**
> galleryDestroy()


### Example

```typescript
import {
    GalleryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GalleryApi(configuration);

let gallery: string; //The gallery ID (default to undefined)

const { status, data } = await apiInstance.galleryDestroy(
    gallery
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **gallery** | [**string**] | The gallery ID | defaults to undefined|


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

# **galleryIndex**
> GalleryIndex200Response galleryIndex()


### Example

```typescript
import {
    GalleryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GalleryApi(configuration);

const { status, data } = await apiInstance.galleryIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**GalleryIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;GalleryResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **galleryShow**
> GalleryStore200Response galleryShow()


### Example

```typescript
import {
    GalleryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GalleryApi(configuration);

let gallery: string; //The gallery ID (default to undefined)

const { status, data } = await apiInstance.galleryShow(
    gallery
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **gallery** | [**string**] | The gallery ID | defaults to undefined|


### Return type

**GalleryStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GalleryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **galleryStore**
> GalleryStore200Response galleryStore(galleryStoreRequest)


### Example

```typescript
import {
    GalleryApi,
    Configuration,
    GalleryStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GalleryApi(configuration);

let galleryStoreRequest: GalleryStoreRequest; //

const { status, data } = await apiInstance.galleryStore(
    galleryStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **galleryStoreRequest** | **GalleryStoreRequest**|  | |


### Return type

**GalleryStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GalleryResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **galleryUpdate**
> GalleryStore200Response galleryUpdate()


### Example

```typescript
import {
    GalleryApi,
    Configuration,
    GalleryUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GalleryApi(configuration);

let gallery: string; //The gallery ID (default to undefined)
let galleryUpdateRequest: GalleryUpdateRequest; // (optional)

const { status, data } = await apiInstance.galleryUpdate(
    gallery,
    galleryUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **galleryUpdateRequest** | **GalleryUpdateRequest**|  | |
| **gallery** | [**string**] | The gallery ID | defaults to undefined|


### Return type

**GalleryStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GalleryResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

