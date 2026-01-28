# ThemeApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**themeDestroy**](#themedestroy) | **DELETE** /theme/{theme} | Remove the specified theme from storage|
|[**themeIndex**](#themeindex) | **GET** /theme | Display a listing of the resource|
|[**themeShow**](#themeshow) | **GET** /theme/{theme} | Display the specified resource|
|[**themeStore**](#themestore) | **POST** /theme | Store a newly created theme in storage|
|[**themeUpdate**](#themeupdate) | **PATCH** /theme/{theme} | Update the specified theme in storage|
|[**themeUpdate2**](#themeupdate2) | **PUT** /theme/{theme} | Update the specified theme in storage|

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

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `translations`, `subthemes`, `subthemes.translations`. (optional) (default to undefined)

const { status, data } = await apiInstance.themeIndex(
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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;translations&#x60;, &#x60;subthemes&#x60;, &#x60;subthemes.translations&#x60;. | (optional) defaults to undefined|


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
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeShow**
> ThemeShow200Response themeShow()


### Example

```typescript
import {
    ThemeApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let theme: string; //The theme ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `translations`, `subthemes`, `subthemes.translations`. (optional) (default to undefined)

const { status, data } = await apiInstance.themeShow(
    theme,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **theme** | [**string**] | The theme ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;translations&#x60;, &#x60;subthemes&#x60;, &#x60;subthemes.translations&#x60;. | (optional) defaults to undefined|


### Return type

**ThemeShow200Response**

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
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeStore**
> ThemeShow200Response themeStore(storeThemeRequest)


### Example

```typescript
import {
    ThemeApi,
    Configuration,
    StoreThemeRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let storeThemeRequest: StoreThemeRequest; //

const { status, data } = await apiInstance.themeStore(
    storeThemeRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeThemeRequest** | **StoreThemeRequest**|  | |


### Return type

**ThemeShow200Response**

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
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeUpdate**
> ThemeShow200Response themeUpdate()


### Example

```typescript
import {
    ThemeApi,
    Configuration,
    UpdateThemeRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let theme: string; //The theme ID (default to undefined)
let updateThemeRequest: UpdateThemeRequest; // (optional)

const { status, data } = await apiInstance.themeUpdate(
    theme,
    updateThemeRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateThemeRequest** | **UpdateThemeRequest**|  | |
| **theme** | [**string**] | The theme ID | defaults to undefined|


### Return type

**ThemeShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **themeUpdate2**
> ThemeShow200Response themeUpdate2()


### Example

```typescript
import {
    ThemeApi,
    Configuration,
    UpdateThemeRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ThemeApi(configuration);

let theme: string; //The theme ID (default to undefined)
let updateThemeRequest: UpdateThemeRequest; // (optional)

const { status, data } = await apiInstance.themeUpdate2(
    theme,
    updateThemeRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateThemeRequest** | **UpdateThemeRequest**|  | |
| **theme** | [**string**] | The theme ID | defaults to undefined|


### Return type

**ThemeShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ThemeResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

