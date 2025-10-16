# LanguageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**languageClearDefault**](#languagecleardefault) | **DELETE** /language/default | Clear the default flag from any language|
|[**languageDestroy**](#languagedestroy) | **DELETE** /language/{language} | Remove the specified resource from storage|
|[**languageGetDefault**](#languagegetdefault) | **GET** /language/default | Get the default Language|
|[**languageGetEnglish**](#languagegetenglish) | **GET** /language/english | Get the english Language|
|[**languageIndex**](#languageindex) | **GET** /language | Display a listing of the resource|
|[**languageSetDefault**](#languagesetdefault) | **PATCH** /language/{language}/default | Set or unset a Language as the default one|
|[**languageShow**](#languageshow) | **GET** /language/{language} | Display the specified resource|
|[**languageStore**](#languagestore) | **POST** /language | Store a newly created resource in storage|
|[**languageUpdate**](#languageupdate) | **PATCH** /language/{language} | Update the specified resource in storage|
|[**languageUpdate2**](#languageupdate2) | **PUT** /language/{language} | Update the specified resource in storage|

# **languageClearDefault**
> ContextClearDefault200Response languageClearDefault()


### Example

```typescript
import {
    LanguageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

const { status, data } = await apiInstance.languageClearDefault();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ContextClearDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;MessageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageDestroy**
> languageDestroy()


### Example

```typescript
import {
    LanguageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)

const { status, data } = await apiInstance.languageDestroy(
    language
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **language** | [**string**] | The language ID | defaults to undefined|


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

# **languageGetDefault**
> LanguageGetDefault200Response languageGetDefault()


### Example

```typescript
import {
    LanguageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

const { status, data } = await apiInstance.languageGetDefault();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**404** |  |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageGetEnglish**
> LanguageGetDefault200Response languageGetEnglish()


### Example

```typescript
import {
    LanguageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

const { status, data } = await apiInstance.languageGetEnglish();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**404** |  |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageIndex**
> LanguageIndex200Response languageIndex()


### Example

```typescript
import {
    LanguageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)

const { status, data } = await apiInstance.languageIndex(
    page,
    perPage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **page** | [**number**] |  | (optional) defaults to undefined|
| **perPage** | [**number**] |  | (optional) defaults to undefined|


### Return type

**LanguageIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;LanguageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageSetDefault**
> LanguageGetDefault200Response languageSetDefault(setDefaultLanguageRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    SetDefaultLanguageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)
let setDefaultLanguageRequest: SetDefaultLanguageRequest; //

const { status, data } = await apiInstance.languageSetDefault(
    language,
    setDefaultLanguageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **setDefaultLanguageRequest** | **SetDefaultLanguageRequest**|  | |
| **language** | [**string**] | The language ID | defaults to undefined|


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageShow**
> LanguageGetDefault200Response languageShow()


### Example

```typescript
import {
    LanguageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)

const { status, data } = await apiInstance.languageShow(
    language
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **language** | [**string**] | The language ID | defaults to undefined|


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageStore**
> LanguageGetDefault200Response languageStore(storeLanguageRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    StoreLanguageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let storeLanguageRequest: StoreLanguageRequest; //

const { status, data } = await apiInstance.languageStore(
    storeLanguageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeLanguageRequest** | **StoreLanguageRequest**|  | |


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageUpdate**
> LanguageGetDefault200Response languageUpdate(updateLanguageRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    UpdateLanguageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)
let updateLanguageRequest: UpdateLanguageRequest; //

const { status, data } = await apiInstance.languageUpdate(
    language,
    updateLanguageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateLanguageRequest** | **UpdateLanguageRequest**|  | |
| **language** | [**string**] | The language ID | defaults to undefined|


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **languageUpdate2**
> LanguageGetDefault200Response languageUpdate2(updateLanguageRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    UpdateLanguageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)
let updateLanguageRequest: UpdateLanguageRequest; //

const { status, data } = await apiInstance.languageUpdate2(
    language,
    updateLanguageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateLanguageRequest** | **UpdateLanguageRequest**|  | |
| **language** | [**string**] | The language ID | defaults to undefined|


### Return type

**LanguageGetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

