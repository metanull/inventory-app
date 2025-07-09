# ThemeApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**themeDestroy**](#themedestroy) | **DELETE** /theme/{theme} | Remove the specified theme from storage|
|[**themeIndex**](#themeindex) | **GET** /theme | Display a listing of the themes for an exhibition|
|[**themeShow**](#themeshow) | **GET** /theme/{theme} | Display the specified theme|
|[**themeStore**](#themestore) | **POST** /theme | Store a newly created theme in storage|
|[**themeUpdate**](#themeupdate) | **PUT** /theme/{theme} | Update the specified theme in storage|

# **themeDestroy**
> themeDestroy()


### Example

```typescript
import {
    ThemeApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let theme: string; //The theme ID (default to undefined)

const { status, data } = await apiInstance.themeDestroy(
    theme
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **theme** | [**string**] | The theme ID | defaults to undefined|


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

# **themeIndex**
> ThemeIndex200Response themeIndex()


### Example

```typescript
import {
    ThemeApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

const { status, data } = await apiInstance.themeIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ThemeIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ThemeResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeShow**
> ThemeStore200Response themeShow()


### Example

```typescript
import {
    ThemeApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let theme: string; //The theme ID (default to undefined)

const { status, data } = await apiInstance.themeShow(
    theme
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **theme** | [**string**] | The theme ID | defaults to undefined|


### Return type

**ThemeStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeStore**
> ThemeStore200Response themeStore(themeStoreRequest)


### Example

```typescript
import {
    ThemeApi,
    Configuration,
    ThemeStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let themeStoreRequest: ThemeStoreRequest; //

const { status, data } = await apiInstance.themeStore(
    themeStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **themeStoreRequest** | **ThemeStoreRequest**|  | |


### Return type

**ThemeStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeUpdate**
> ThemeStore200Response themeUpdate()


### Example

```typescript
import {
    ThemeApi,
    Configuration,
    ExhibitionUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let theme: string; //The theme ID (default to undefined)
let exhibitionUpdateRequest: ExhibitionUpdateRequest; // (optional)

const { status, data } = await apiInstance.themeUpdate(
    theme,
    exhibitionUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **exhibitionUpdateRequest** | **ExhibitionUpdateRequest**|  | |
| **theme** | [**string**] | The theme ID | defaults to undefined|


### Return type

**ThemeStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

