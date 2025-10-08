# AvailableImageApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**availableImageDestroy**](#availableimagedestroy) | **DELETE** /available-image/{availableImage} | Remove the specified resource from storage|
|[**availableImageDownload**](#availableimagedownload) | **GET** /available-image/{availableImage}/download | Returns the file to the caller|
|[**availableImageIndex**](#availableimageindex) | **GET** /available-image | Display a listing of the resource|
|[**availableImageShow**](#availableimageshow) | **GET** /available-image/{availableImage} | Display the specified resource|
|[**availableImageUpdate**](#availableimageupdate) | **PUT** /available-image/{availableImage} | Update the specified resource in storage|
|[**availableImageView**](#availableimageview) | **GET** /available-image/{availableImage}/view | Returns the image file for direct viewing (e.g., for use in &lt;img&gt; src attribute)|

# **availableImageDestroy**
> availableImageDestroy()


### Example

```typescript
import {
    AvailableImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AvailableImageApi(configuration);

let availableImage: string; //The available image ID (default to undefined)

const { status, data } = await apiInstance.availableImageDestroy(
    availableImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **availableImage** | [**string**] | The available image ID | defaults to undefined|


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

# **availableImageDownload**
> File availableImageDownload()


### Example

```typescript
import {
    AvailableImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AvailableImageApi(configuration);

let availableImage: string; //The available image ID (default to undefined)

const { status, data } = await apiInstance.availableImageDownload(
    availableImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **availableImage** | [**string**] | The available image ID | defaults to undefined|


### Return type

**File**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/octet-stream, application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  * Content-Disposition -  <br>  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **availableImageIndex**
> AvailableImageIndex200Response availableImageIndex()


### Example

```typescript
import {
    AvailableImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AvailableImageApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.availableImageIndex(
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

**AvailableImageIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;AvailableImageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **availableImageShow**
> AvailableImageShow200Response availableImageShow()


### Example

```typescript
import {
    AvailableImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AvailableImageApi(configuration);

let availableImage: string; //The available image ID (default to undefined)

const { status, data } = await apiInstance.availableImageShow(
    availableImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **availableImage** | [**string**] | The available image ID | defaults to undefined|


### Return type

**AvailableImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AvailableImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **availableImageUpdate**
> AvailableImageShow200Response availableImageUpdate()


### Example

```typescript
import {
    AvailableImageApi,
    Configuration,
    AvailableImageUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new AvailableImageApi(configuration);

let availableImage: string; //The available image ID (default to undefined)
let availableImageUpdateRequest: AvailableImageUpdateRequest; // (optional)

const { status, data } = await apiInstance.availableImageUpdate(
    availableImage,
    availableImageUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **availableImageUpdateRequest** | **AvailableImageUpdateRequest**|  | |
| **availableImage** | [**string**] | The available image ID | defaults to undefined|


### Return type

**AvailableImageShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;AvailableImageResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **availableImageView**
> File availableImageView()


### Example

```typescript
import {
    AvailableImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new AvailableImageApi(configuration);

let availableImage: string; //The available image ID (default to undefined)

const { status, data } = await apiInstance.availableImageView(
    availableImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **availableImage** | [**string**] | The available image ID | defaults to undefined|


### Return type

**File**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/octet-stream, application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

