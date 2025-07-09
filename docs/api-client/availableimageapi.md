---
layout: default
title: "AvailableImageApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# AvailableImageApi

All URIs are relative to *http://localhost:8000/api*

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **availableImageDownload**
> string availableImageDownload()


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

**string**

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

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

const { status, data } = await apiInstance.availableImageIndex();
```

### Parameters
This endpoint does not have any parameters.


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
|**200** | Array of &#x60;AvailableImageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **availableImageView**
> string availableImageView()


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

**string**

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
