# LanguageTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**languageTranslationDestroy**](#languagetranslationdestroy) | **DELETE** /language-translation/{languageTranslation} | Remove the specified resource from storage|
|[**languageTranslationIndex**](#languagetranslationindex) | **GET** /language-translation | Display a listing of the resource|
|[**languageTranslationShow**](#languagetranslationshow) | **GET** /language-translation/{languageTranslation} | Display the specified resource|
|[**languageTranslationStore**](#languagetranslationstore) | **POST** /language-translation | Store a newly created resource in storage|
|[**languageTranslationUpdate**](#languagetranslationupdate) | **PATCH** /language-translation/{languageTranslation} | Update the specified resource in storage|
|[**languageTranslationUpdate2**](#languagetranslationupdate2) | **PUT** /language-translation/{languageTranslation} | Update the specified resource in storage|

# **languageTranslationDestroy**
> languageTranslationDestroy()


### Example

```typescript
import {
    LanguageTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageTranslationApi(configuration);

let languageTranslation: string; //The language translation ID (default to undefined)

const { status, data } = await apiInstance.languageTranslationDestroy(
    languageTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **languageTranslation** | [**string**] | The language translation ID | defaults to undefined|


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

# **languageTranslationIndex**
> LanguageTranslationIndex200Response languageTranslationIndex()


### Example

```typescript
import {
    LanguageTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `language`, `displayLanguage`. (optional) (default to undefined)

const { status, data } = await apiInstance.languageTranslationIndex(
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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;language&#x60;, &#x60;displayLanguage&#x60;. | (optional) defaults to undefined|


### Return type

**LanguageTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;LanguageTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageTranslationShow**
> LanguageTranslationShow200Response languageTranslationShow()


### Example

```typescript
import {
    LanguageTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageTranslationApi(configuration);

let languageTranslation: string; //The language translation ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `language`, `displayLanguage`. (optional) (default to undefined)

const { status, data } = await apiInstance.languageTranslationShow(
    languageTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **languageTranslation** | [**string**] | The language translation ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;language&#x60;, &#x60;displayLanguage&#x60;. | (optional) defaults to undefined|


### Return type

**LanguageTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageTranslationStore**
> LanguageTranslationShow200Response languageTranslationStore(storeLanguageTranslationRequest)


### Example

```typescript
import {
    LanguageTranslationApi,
    Configuration,
    StoreLanguageTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageTranslationApi(configuration);

let storeLanguageTranslationRequest: StoreLanguageTranslationRequest; //

const { status, data } = await apiInstance.languageTranslationStore(
    storeLanguageTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeLanguageTranslationRequest** | **StoreLanguageTranslationRequest**|  | |


### Return type

**LanguageTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageTranslationUpdate**
> LanguageTranslationShow200Response languageTranslationUpdate()


### Example

```typescript
import {
    LanguageTranslationApi,
    Configuration,
    UpdateLanguageTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageTranslationApi(configuration);

let languageTranslation: string; //The language translation ID (default to undefined)
let updateLanguageTranslationRequest: UpdateLanguageTranslationRequest; // (optional)

const { status, data } = await apiInstance.languageTranslationUpdate(
    languageTranslation,
    updateLanguageTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateLanguageTranslationRequest** | **UpdateLanguageTranslationRequest**|  | |
| **languageTranslation** | [**string**] | The language translation ID | defaults to undefined|


### Return type

**LanguageTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageTranslationUpdate2**
> LanguageTranslationShow200Response languageTranslationUpdate2()


### Example

```typescript
import {
    LanguageTranslationApi,
    Configuration,
    UpdateLanguageTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageTranslationApi(configuration);

let languageTranslation: string; //The language translation ID (default to undefined)
let updateLanguageTranslationRequest: UpdateLanguageTranslationRequest; // (optional)

const { status, data } = await apiInstance.languageTranslationUpdate2(
    languageTranslation,
    updateLanguageTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateLanguageTranslationRequest** | **UpdateLanguageTranslationRequest**|  | |
| **languageTranslation** | [**string**] | The language translation ID | defaults to undefined|


### Return type

**LanguageTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

