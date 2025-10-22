# GlossaryApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**glossaryAttachSynonym**](#glossaryattachsynonym) | **POST** /glossary/{glossary}/attach-synonym | Attach a synonym to the glossary entry|
|[**glossaryDestroy**](#glossarydestroy) | **DELETE** /glossary/{glossary} | Remove the specified resource from storage|
|[**glossaryDetachSynonym**](#glossarydetachsynonym) | **DELETE** /glossary/{glossary}/detach-synonym | Detach a synonym from the glossary entry|
|[**glossaryIndex**](#glossaryindex) | **GET** /glossary | Display a listing of the resource|
|[**glossaryShow**](#glossaryshow) | **GET** /glossary/{glossary} | Display the specified resource|
|[**glossaryStore**](#glossarystore) | **POST** /glossary | Store a newly created resource in storage|
|[**glossaryUpdate**](#glossaryupdate) | **PATCH** /glossary/{glossary} | Update the specified resource in storage|
|[**glossaryUpdate2**](#glossaryupdate2) | **PUT** /glossary/{glossary} | Update the specified resource in storage|

# **glossaryAttachSynonym**
> GlossaryShow200Response glossaryAttachSynonym(attachGlossarySynonymRequest)


### Example

```typescript
import {
    GlossaryApi,
    Configuration,
    AttachGlossarySynonymRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let glossary: string; //The glossary ID (default to undefined)
let attachGlossarySynonymRequest: AttachGlossarySynonymRequest; //

const { status, data } = await apiInstance.glossaryAttachSynonym(
    glossary,
    attachGlossarySynonymRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachGlossarySynonymRequest** | **AttachGlossarySynonymRequest**|  | |
| **glossary** | [**string**] | The glossary ID | defaults to undefined|


### Return type

**GlossaryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryDestroy**
> glossaryDestroy()


### Example

```typescript
import {
    GlossaryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let glossary: string; //The glossary ID (default to undefined)

const { status, data } = await apiInstance.glossaryDestroy(
    glossary
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossary** | [**string**] | The glossary ID | defaults to undefined|


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

# **glossaryDetachSynonym**
> GlossaryShow200Response glossaryDetachSynonym()


### Example

```typescript
import {
    GlossaryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let glossary: string; //The glossary ID (default to undefined)
let synonymId: string; // (default to undefined)

const { status, data } = await apiInstance.glossaryDetachSynonym(
    glossary,
    synonymId
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossary** | [**string**] | The glossary ID | defaults to undefined|
| **synonymId** | [**string**] |  | defaults to undefined|


### Return type

**GlossaryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryIndex**
> GlossaryIndex200Response glossaryIndex()


### Example

```typescript
import {
    GlossaryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.glossaryIndex(
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

**GlossaryIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;GlossaryResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryShow**
> GlossaryShow200Response glossaryShow()


### Example

```typescript
import {
    GlossaryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let glossary: string; //The glossary ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.glossaryShow(
    glossary,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossary** | [**string**] | The glossary ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**GlossaryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryStore**
> GlossaryShow200Response glossaryStore(storeGlossaryRequest)


### Example

```typescript
import {
    GlossaryApi,
    Configuration,
    StoreGlossaryRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let storeGlossaryRequest: StoreGlossaryRequest; //

const { status, data } = await apiInstance.glossaryStore(
    storeGlossaryRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeGlossaryRequest** | **StoreGlossaryRequest**|  | |


### Return type

**GlossaryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryUpdate**
> GlossaryShow200Response glossaryUpdate(updateGlossaryRequest)


### Example

```typescript
import {
    GlossaryApi,
    Configuration,
    UpdateGlossaryRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let glossary: string; //The glossary ID (default to undefined)
let updateGlossaryRequest: UpdateGlossaryRequest; //

const { status, data } = await apiInstance.glossaryUpdate(
    glossary,
    updateGlossaryRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateGlossaryRequest** | **UpdateGlossaryRequest**|  | |
| **glossary** | [**string**] | The glossary ID | defaults to undefined|


### Return type

**GlossaryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryUpdate2**
> GlossaryShow200Response glossaryUpdate2(updateGlossaryRequest)


### Example

```typescript
import {
    GlossaryApi,
    Configuration,
    UpdateGlossaryRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryApi(configuration);

let glossary: string; //The glossary ID (default to undefined)
let updateGlossaryRequest: UpdateGlossaryRequest; //

const { status, data } = await apiInstance.glossaryUpdate2(
    glossary,
    updateGlossaryRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateGlossaryRequest** | **UpdateGlossaryRequest**|  | |
| **glossary** | [**string**] | The glossary ID | defaults to undefined|


### Return type

**GlossaryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

