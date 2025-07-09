---
layout: default
title: "LanguageApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# LanguageApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**languageDestroy**](#languagedestroy) | **DELETE** /language/{language} | Remove the specified resource from storage|
|[**languageGetDefault**](#languagegetdefault) | **GET** /language/default | Get the default Language|
|[**languageGetEnglish**](#languagegetenglish) | **GET** /language/english | Get the english Language|
|[**languageIndex**](#languageindex) | **GET** /language | Display a listing of the resource|
|[**languageSetDefault**](#languagesetdefault) | **PATCH** /language/{language}/default | Set a Language as the default one|
|[**languageShow**](#languageshow) | **GET** /language/{language} | Display the specified resource|
|[**languageStore**](#languagestore) | **POST** /language | Store a newly created resource in storage|
|[**languageUpdate**](#languageupdate) | **PUT** /language/{language} | Update the specified resource in storage|

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **languageGetDefault**
> LanguageSetDefault200Response languageGetDefault()


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

**LanguageSetDefault200Response**

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **languageGetEnglish**
> LanguageSetDefault200Response languageGetEnglish()


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

**LanguageSetDefault200Response**

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

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

const { status, data } = await apiInstance.languageIndex();
```

### Parameters
This endpoint does not have any parameters.


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
|**200** | Array of &#x60;LanguageResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **languageSetDefault**
> LanguageSetDefault200Response languageSetDefault(contextSetDefaultRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    ContextSetDefaultRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)
let contextSetDefaultRequest: ContextSetDefaultRequest; //

const { status, data } = await apiInstance.languageSetDefault(
    language,
    contextSetDefaultRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contextSetDefaultRequest** | **ContextSetDefaultRequest**|  | |
| **language** | [**string**] | The language ID | defaults to undefined|


### Return type

**LanguageSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **languageShow**
> LanguageSetDefault200Response languageShow()


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

**LanguageSetDefault200Response**

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **languageStore**
> LanguageSetDefault200Response languageStore(languageStoreRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    LanguageStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let languageStoreRequest: LanguageStoreRequest; //

const { status, data } = await apiInstance.languageStore(
    languageStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **languageStoreRequest** | **LanguageStoreRequest**|  | |


### Return type

**LanguageSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **languageUpdate**
> LanguageSetDefault200Response languageUpdate(languageUpdateRequest)


### Example

```typescript
import {
    LanguageApi,
    Configuration,
    LanguageUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new LanguageApi(configuration);

let language: string; //The language ID (default to undefined)
let languageUpdateRequest: LanguageUpdateRequest; //

const { status, data } = await apiInstance.languageUpdate(
    language,
    languageUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **languageUpdateRequest** | **LanguageUpdateRequest**|  | |
| **language** | [**string**] | The language ID | defaults to undefined|


### Return type

**LanguageSetDefault200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;LanguageResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
