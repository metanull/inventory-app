# GlossaryTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**glossaryTranslationDestroy**](#glossarytranslationdestroy) | **DELETE** /glossary-translation/{glossaryTranslation} | Remove the specified resource from storage|
|[**glossaryTranslationIndex**](#glossarytranslationindex) | **GET** /glossary-translation | Display a listing of the resource|
|[**glossaryTranslationShow**](#glossarytranslationshow) | **GET** /glossary-translation/{glossaryTranslation} | Display the specified resource|
|[**glossaryTranslationStore**](#glossarytranslationstore) | **POST** /glossary-translation | Store a newly created resource in storage|
|[**glossaryTranslationUpdate**](#glossarytranslationupdate) | **PATCH** /glossary-translation/{glossaryTranslation} | Update the specified resource in storage|
|[**glossaryTranslationUpdate2**](#glossarytranslationupdate2) | **PUT** /glossary-translation/{glossaryTranslation} | Update the specified resource in storage|

# **glossaryTranslationDestroy**
> glossaryTranslationDestroy()


### Example

```typescript
import {
    GlossaryTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryTranslationApi(configuration);

let glossaryTranslation: string; //The glossary translation ID (default to undefined)

const { status, data } = await apiInstance.glossaryTranslationDestroy(
    glossaryTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossaryTranslation** | [**string**] | The glossary translation ID | defaults to undefined|


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

# **glossaryTranslationIndex**
> GlossaryTranslationIndex200Response glossaryTranslationIndex()


### Example

```typescript
import {
    GlossaryTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.glossaryTranslationIndex(
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

**GlossaryTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;GlossaryTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryTranslationShow**
> GlossaryTranslationShow200Response glossaryTranslationShow()


### Example

```typescript
import {
    GlossaryTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryTranslationApi(configuration);

let glossaryTranslation: string; //The glossary translation ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.glossaryTranslationShow(
    glossaryTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossaryTranslation** | [**string**] | The glossary translation ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**GlossaryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryTranslationStore**
> GlossaryTranslationShow200Response glossaryTranslationStore(storeGlossaryTranslationRequest)


### Example

```typescript
import {
    GlossaryTranslationApi,
    Configuration,
    StoreGlossaryTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryTranslationApi(configuration);

let storeGlossaryTranslationRequest: StoreGlossaryTranslationRequest; //

const { status, data } = await apiInstance.glossaryTranslationStore(
    storeGlossaryTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeGlossaryTranslationRequest** | **StoreGlossaryTranslationRequest**|  | |


### Return type

**GlossaryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryTranslationUpdate**
> GlossaryTranslationShow200Response glossaryTranslationUpdate()


### Example

```typescript
import {
    GlossaryTranslationApi,
    Configuration,
    UpdateGlossaryTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryTranslationApi(configuration);

let glossaryTranslation: string; //The glossary translation ID (default to undefined)
let updateGlossaryTranslationRequest: UpdateGlossaryTranslationRequest; // (optional)

const { status, data } = await apiInstance.glossaryTranslationUpdate(
    glossaryTranslation,
    updateGlossaryTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateGlossaryTranslationRequest** | **UpdateGlossaryTranslationRequest**|  | |
| **glossaryTranslation** | [**string**] | The glossary translation ID | defaults to undefined|


### Return type

**GlossaryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossaryTranslationUpdate2**
> GlossaryTranslationShow200Response glossaryTranslationUpdate2()


### Example

```typescript
import {
    GlossaryTranslationApi,
    Configuration,
    UpdateGlossaryTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossaryTranslationApi(configuration);

let glossaryTranslation: string; //The glossary translation ID (default to undefined)
let updateGlossaryTranslationRequest: UpdateGlossaryTranslationRequest; // (optional)

const { status, data } = await apiInstance.glossaryTranslationUpdate2(
    glossaryTranslation,
    updateGlossaryTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateGlossaryTranslationRequest** | **UpdateGlossaryTranslationRequest**|  | |
| **glossaryTranslation** | [**string**] | The glossary translation ID | defaults to undefined|


### Return type

**GlossaryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossaryTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

