# ExhibitionTranslationsApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**exhibitionTranslationDestroy**](#exhibitiontranslationdestroy) | **DELETE** /exhibition-translation/{exhibitionTranslation} | Remove the specified exhibition translation|
|[**exhibitionTranslationIndex**](#exhibitiontranslationindex) | **GET** /exhibition-translation | Display a listing of exhibition translations|
|[**exhibitionTranslationShow**](#exhibitiontranslationshow) | **GET** /exhibition-translation/{exhibitionTranslation} | Display the specified exhibition translation|
|[**exhibitionTranslationStore**](#exhibitiontranslationstore) | **POST** /exhibition-translation | Store a newly created exhibition translation|
|[**exhibitionTranslationUpdate**](#exhibitiontranslationupdate) | **PUT** /exhibition-translation/{exhibitionTranslation} | Update the specified exhibition translation|

# **exhibitionTranslationDestroy**
> number exhibitionTranslationDestroy()


### Example

```typescript
import {
    ExhibitionTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionTranslationsApi(configuration);

let exhibitionTranslation: string; //The exhibition translation ID (default to undefined)

const { status, data } = await apiInstance.exhibitionTranslationDestroy(
    exhibitionTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionTranslation** | [**string**] | The exhibition translation ID | defaults to undefined|


### Return type

**number**

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

# **exhibitionTranslationIndex**
> Array<ExhibitionTranslationResource> exhibitionTranslationIndex()


### Example

```typescript
import {
    ExhibitionTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionTranslationsApi(configuration);

const { status, data } = await apiInstance.exhibitionTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**Array<ExhibitionTranslationResource>**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **exhibitionTranslationShow**
> ExhibitionTranslationStore200Response exhibitionTranslationShow()


### Example

```typescript
import {
    ExhibitionTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionTranslationsApi(configuration);

let exhibitionTranslation: string; //The exhibition translation ID (default to undefined)

const { status, data } = await apiInstance.exhibitionTranslationShow(
    exhibitionTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionTranslation** | [**string**] | The exhibition translation ID | defaults to undefined|


### Return type

**ExhibitionTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ExhibitionTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **exhibitionTranslationStore**
> ExhibitionTranslationStore200Response exhibitionTranslationStore(exhibitionTranslationStoreRequest)


### Example

```typescript
import {
    ExhibitionTranslationsApi,
    Configuration,
    ExhibitionTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionTranslationsApi(configuration);

let exhibitionTranslationStoreRequest: ExhibitionTranslationStoreRequest; //

const { status, data } = await apiInstance.exhibitionTranslationStore(
    exhibitionTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionTranslationStoreRequest** | **ExhibitionTranslationStoreRequest**|  | |


### Return type

**ExhibitionTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ExhibitionTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **exhibitionTranslationUpdate**
> ExhibitionTranslationStore200Response exhibitionTranslationUpdate()


### Example

```typescript
import {
    ExhibitionTranslationsApi,
    Configuration,
    ExhibitionTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ExhibitionTranslationsApi(configuration);

let exhibitionTranslation: string; //The exhibition translation ID (default to undefined)
let exhibitionTranslationUpdateRequest: ExhibitionTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.exhibitionTranslationUpdate(
    exhibitionTranslation,
    exhibitionTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionTranslationUpdateRequest** | **ExhibitionTranslationUpdateRequest**|  | |
| **exhibitionTranslation** | [**string**] | The exhibition translation ID | defaults to undefined|


### Return type

**ExhibitionTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ExhibitionTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

