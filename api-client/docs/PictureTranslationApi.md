# PictureTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**pictureTranslationDestroy**](#picturetranslationdestroy) | **DELETE** /picture-translation/{pictureTranslation} | Remove the specified resource from storage|
|[**pictureTranslationIndex**](#picturetranslationindex) | **GET** /picture-translation | Display a listing of the resource|
|[**pictureTranslationShow**](#picturetranslationshow) | **GET** /picture-translation/{pictureTranslation} | Display the specified resource|
|[**pictureTranslationStore**](#picturetranslationstore) | **POST** /picture-translation | Store a newly created resource in storage|
|[**pictureTranslationUpdate**](#picturetranslationupdate) | **PUT** /picture-translation/{pictureTranslation} | Update the specified resource in storage|

# **pictureTranslationDestroy**
> pictureTranslationDestroy()


### Example

```typescript
import {
    PictureTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PictureTranslationApi(configuration);

let pictureTranslation: string; //The picture translation ID (default to undefined)

const { status, data } = await apiInstance.pictureTranslationDestroy(
    pictureTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **pictureTranslation** | [**string**] | The picture translation ID | defaults to undefined|


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

# **pictureTranslationIndex**
> PictureTranslationIndex200Response pictureTranslationIndex()


### Example

```typescript
import {
    PictureTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PictureTranslationApi(configuration);

const { status, data } = await apiInstance.pictureTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**PictureTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;PictureTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pictureTranslationShow**
> PictureTranslationStore200Response pictureTranslationShow()


### Example

```typescript
import {
    PictureTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PictureTranslationApi(configuration);

let pictureTranslation: string; //The picture translation ID (default to undefined)

const { status, data } = await apiInstance.pictureTranslationShow(
    pictureTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **pictureTranslation** | [**string**] | The picture translation ID | defaults to undefined|


### Return type

**PictureTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PictureTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pictureTranslationStore**
> PictureTranslationStore200Response pictureTranslationStore(pictureTranslationStoreRequest)


### Example

```typescript
import {
    PictureTranslationApi,
    Configuration,
    PictureTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PictureTranslationApi(configuration);

let pictureTranslationStoreRequest: PictureTranslationStoreRequest; //

const { status, data } = await apiInstance.pictureTranslationStore(
    pictureTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **pictureTranslationStoreRequest** | **PictureTranslationStoreRequest**|  | |


### Return type

**PictureTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PictureTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **pictureTranslationUpdate**
> PictureTranslationStore200Response pictureTranslationUpdate()


### Example

```typescript
import {
    PictureTranslationApi,
    Configuration,
    PictureTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PictureTranslationApi(configuration);

let pictureTranslation: string; //The picture translation ID (default to undefined)
let pictureTranslationUpdateRequest: PictureTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.pictureTranslationUpdate(
    pictureTranslation,
    pictureTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **pictureTranslationUpdateRequest** | **PictureTranslationUpdateRequest**|  | |
| **pictureTranslation** | [**string**] | The picture translation ID | defaults to undefined|


### Return type

**PictureTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PictureTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

