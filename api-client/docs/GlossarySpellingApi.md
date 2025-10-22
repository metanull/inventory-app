# GlossarySpellingApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**glossarySpellingDestroy**](#glossaryspellingdestroy) | **DELETE** /glossary-spelling/{glossarySpelling} | Remove the specified resource from storage|
|[**glossarySpellingIndex**](#glossaryspellingindex) | **GET** /glossary-spelling | Display a listing of the resource|
|[**glossarySpellingShow**](#glossaryspellingshow) | **GET** /glossary-spelling/{glossarySpelling} | Display the specified resource|
|[**glossarySpellingStore**](#glossaryspellingstore) | **POST** /glossary-spelling | Store a newly created resource in storage|
|[**glossarySpellingUpdate**](#glossaryspellingupdate) | **PATCH** /glossary-spelling/{glossarySpelling} | Update the specified resource in storage|
|[**glossarySpellingUpdate2**](#glossaryspellingupdate2) | **PUT** /glossary-spelling/{glossarySpelling} | Update the specified resource in storage|

# **glossarySpellingDestroy**
> glossarySpellingDestroy()


### Example

```typescript
import {
    GlossarySpellingApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossarySpellingApi(configuration);

let glossarySpelling: string; //The glossary spelling ID (default to undefined)

const { status, data } = await apiInstance.glossarySpellingDestroy(
    glossarySpelling
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossarySpelling** | [**string**] | The glossary spelling ID | defaults to undefined|


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

# **glossarySpellingIndex**
> GlossarySpellingIndex200Response glossarySpellingIndex()


### Example

```typescript
import {
    GlossarySpellingApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossarySpellingApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.glossarySpellingIndex(
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

**GlossarySpellingIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;GlossarySpellingResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossarySpellingShow**
> GlossarySpellingShow200Response glossarySpellingShow()


### Example

```typescript
import {
    GlossarySpellingApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossarySpellingApi(configuration);

let glossarySpelling: string; //The glossary spelling ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.glossarySpellingShow(
    glossarySpelling,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **glossarySpelling** | [**string**] | The glossary spelling ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**GlossarySpellingShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossarySpellingResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossarySpellingStore**
> GlossarySpellingShow200Response glossarySpellingStore(storeGlossarySpellingRequest)


### Example

```typescript
import {
    GlossarySpellingApi,
    Configuration,
    StoreGlossarySpellingRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossarySpellingApi(configuration);

let storeGlossarySpellingRequest: StoreGlossarySpellingRequest; //

const { status, data } = await apiInstance.glossarySpellingStore(
    storeGlossarySpellingRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeGlossarySpellingRequest** | **StoreGlossarySpellingRequest**|  | |


### Return type

**GlossarySpellingShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossarySpellingResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossarySpellingUpdate**
> GlossarySpellingShow200Response glossarySpellingUpdate()


### Example

```typescript
import {
    GlossarySpellingApi,
    Configuration,
    UpdateGlossarySpellingRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossarySpellingApi(configuration);

let glossarySpelling: string; //The glossary spelling ID (default to undefined)
let updateGlossarySpellingRequest: UpdateGlossarySpellingRequest; // (optional)

const { status, data } = await apiInstance.glossarySpellingUpdate(
    glossarySpelling,
    updateGlossarySpellingRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateGlossarySpellingRequest** | **UpdateGlossarySpellingRequest**|  | |
| **glossarySpelling** | [**string**] | The glossary spelling ID | defaults to undefined|


### Return type

**GlossarySpellingShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossarySpellingResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **glossarySpellingUpdate2**
> GlossarySpellingShow200Response glossarySpellingUpdate2()


### Example

```typescript
import {
    GlossarySpellingApi,
    Configuration,
    UpdateGlossarySpellingRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new GlossarySpellingApi(configuration);

let glossarySpelling: string; //The glossary spelling ID (default to undefined)
let updateGlossarySpellingRequest: UpdateGlossarySpellingRequest; // (optional)

const { status, data } = await apiInstance.glossarySpellingUpdate2(
    glossarySpelling,
    updateGlossarySpellingRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateGlossarySpellingRequest** | **UpdateGlossarySpellingRequest**|  | |
| **glossarySpelling** | [**string**] | The glossary spelling ID | defaults to undefined|


### Return type

**GlossarySpellingShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;GlossarySpellingResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

