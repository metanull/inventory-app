# ImageUploadApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**imageUploadDestroy**](#imageuploaddestroy) | **DELETE** /image-upload/{imageUpload} | Remove the specified resource from storage|
|[**imageUploadIndex**](#imageuploadindex) | **GET** /image-upload | Display a listing of the resource|
|[**imageUploadShow**](#imageuploadshow) | **GET** /image-upload/{imageUpload} | Display the specified resource|
|[**imageUploadStatus**](#imageuploadstatus) | **GET** /image-upload/{id}/status | Get the processing status of an image upload|
|[**imageUploadStore**](#imageuploadstore) | **POST** /image-upload | Store a newly created resource in storage|

# **imageUploadDestroy**
> imageUploadDestroy()


### Example

```typescript
import {
    ImageUploadApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ImageUploadApi(configuration);

let imageUpload: string; //The image upload ID (default to undefined)

const { status, data } = await apiInstance.imageUploadDestroy(
    imageUpload
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **imageUpload** | [**string**] | The image upload ID | defaults to undefined|


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

# **imageUploadIndex**
> ImageUploadIndex200Response imageUploadIndex()


### Example

```typescript
import {
    ImageUploadApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ImageUploadApi(configuration);

const { status, data } = await apiInstance.imageUploadIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ImageUploadIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ImageUploadResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **imageUploadShow**
> ImageUploadStore200Response imageUploadShow()


### Example

```typescript
import {
    ImageUploadApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ImageUploadApi(configuration);

let imageUpload: string; //The image upload ID (default to undefined)

const { status, data } = await apiInstance.imageUploadShow(
    imageUpload
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **imageUpload** | [**string**] | The image upload ID | defaults to undefined|


### Return type

**ImageUploadStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ImageUploadResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **imageUploadStatus**
> ImageUploadStatus200Response imageUploadStatus()

Returns the processing status. If processing is complete, returns the AvailableImage details. If the ImageUpload no longer exists, check if an AvailableImage exists with the same ID.

### Example

```typescript
import {
    ImageUploadApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ImageUploadApi(configuration);

let id: string; // (default to undefined)

const { status, data } = await apiInstance.imageUploadStatus(
    id
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **id** | [**string**] |  | defaults to undefined|


### Return type

**ImageUploadStatus200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**404** | Neither exists - this could be an error state or the resource was never created |  -  |
|**200** |  |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **imageUploadStore**
> ImageUploadStore200Response imageUploadStore()


### Example

```typescript
import {
    ImageUploadApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ImageUploadApi(configuration);

let file: File; // (default to undefined)

const { status, data } = await apiInstance.imageUploadStore(
    file
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **file** | [**File**] |  | defaults to undefined|


### Return type

**ImageUploadStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: multipart/form-data
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ImageUploadResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

