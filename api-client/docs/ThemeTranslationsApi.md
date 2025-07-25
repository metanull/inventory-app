# ThemeTranslationsApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**themeTranslationDestroy**](#themetranslationdestroy) | **DELETE** /theme-translation/{themeTranslation} | Remove the specified theme translation|
|[**themeTranslationIndex**](#themetranslationindex) | **GET** /theme-translation | Display a listing of theme translations|
|[**themeTranslationShow**](#themetranslationshow) | **GET** /theme-translation/{themeTranslation} | Display the specified theme translation|
|[**themeTranslationStore**](#themetranslationstore) | **POST** /theme-translation | Store a newly created theme translation|
|[**themeTranslationUpdate**](#themetranslationupdate) | **PUT** /theme-translation/{themeTranslation} | Update the specified theme translation|

# **themeTranslationDestroy**
> number themeTranslationDestroy()


### Example

```typescript
import {
    ThemeTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeTranslationsApi(configuration);

let themeTranslation: string; //The theme translation ID (default to undefined)

const { status, data } = await apiInstance.themeTranslationDestroy(
    themeTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **themeTranslation** | [**string**] | The theme translation ID | defaults to undefined|


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

# **themeTranslationIndex**
> Array<ThemeTranslationResource> themeTranslationIndex()


### Example

```typescript
import {
    ThemeTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeTranslationsApi(configuration);

const { status, data } = await apiInstance.themeTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**Array<ThemeTranslationResource>**

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

# **themeTranslationShow**
> ThemeTranslationStore200Response themeTranslationShow()


### Example

```typescript
import {
    ThemeTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeTranslationsApi(configuration);

let themeTranslation: string; //The theme translation ID (default to undefined)

const { status, data } = await apiInstance.themeTranslationShow(
    themeTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **themeTranslation** | [**string**] | The theme translation ID | defaults to undefined|


### Return type

**ThemeTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeTranslationStore**
> ThemeTranslationStore200Response themeTranslationStore(themeTranslationStoreRequest)


### Example

```typescript
import {
    ThemeTranslationsApi,
    Configuration,
    ThemeTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeTranslationsApi(configuration);

let themeTranslationStoreRequest: ThemeTranslationStoreRequest; //

const { status, data } = await apiInstance.themeTranslationStore(
    themeTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **themeTranslationStoreRequest** | **ThemeTranslationStoreRequest**|  | |


### Return type

**ThemeTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeTranslationUpdate**
> ThemeTranslationStore200Response themeTranslationUpdate()


### Example

```typescript
import {
    ThemeTranslationsApi,
    Configuration,
    ThemeTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeTranslationsApi(configuration);

let themeTranslation: string; //The theme translation ID (default to undefined)
let themeTranslationUpdateRequest: ThemeTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.themeTranslationUpdate(
    themeTranslation,
    themeTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **themeTranslationUpdateRequest** | **ThemeTranslationUpdateRequest**|  | |
| **themeTranslation** | [**string**] | The theme translation ID | defaults to undefined|


### Return type

**ThemeTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

