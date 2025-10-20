# CollectionTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**collectionTranslationDestroy**](#collectiontranslationdestroy) | **DELETE** /collection-translation/{collectionTranslation} | Remove the specified collection translation|
|[**collectionTranslationIndex**](#collectiontranslationindex) | **GET** /collection-translation | Display a listing of collection translations|
|[**collectionTranslationShow**](#collectiontranslationshow) | **GET** /collection-translation/{collectionTranslation} | Display the specified collection translation|
|[**collectionTranslationStore**](#collectiontranslationstore) | **POST** /collection-translation | Store a newly created collection translation|
|[**collectionTranslationUpdate**](#collectiontranslationupdate) | **PATCH** /collection-translation/{collectionTranslation} | Update the specified collection translation|
|[**collectionTranslationUpdate2**](#collectiontranslationupdate2) | **PUT** /collection-translation/{collectionTranslation} | Update the specified collection translation|

# **collectionTranslationDestroy**
> collectionTranslationDestroy()


### Example

```typescript
import {
    CollectionTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionTranslationApi(configuration);

let collectionTranslation: string; //The collection translation ID (default to undefined)

const { status, data } = await apiInstance.collectionTranslationDestroy(
    collectionTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionTranslation** | [**string**] | The collection translation ID | defaults to undefined|


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

# **collectionTranslationIndex**
> CollectionTranslationIndex200Response collectionTranslationIndex()


### Example

```typescript
import {
    CollectionTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let collectionId: string; // (optional) (default to undefined)
let languageId: string; // (optional) (default to undefined)
let contextId: string; // (optional) (default to undefined)
let defaultContext: boolean; // (optional) (default to undefined)

const { status, data } = await apiInstance.collectionTranslationIndex(
    page,
    perPage,
    collectionId,
    languageId,
    contextId,
    defaultContext
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **page** | [**number**] |  | (optional) defaults to undefined|
| **perPage** | [**number**] |  | (optional) defaults to undefined|
| **collectionId** | [**string**] |  | (optional) defaults to undefined|
| **languageId** | [**string**] |  | (optional) defaults to undefined|
| **contextId** | [**string**] |  | (optional) defaults to undefined|
| **defaultContext** | [**boolean**] |  | (optional) defaults to undefined|


### Return type

**CollectionTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;CollectionTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionTranslationShow**
> CollectionTranslationShow200Response collectionTranslationShow()


### Example

```typescript
import {
    CollectionTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionTranslationApi(configuration);

let collectionTranslation: string; //The collection translation ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.collectionTranslationShow(
    collectionTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionTranslation** | [**string**] | The collection translation ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**CollectionTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionTranslationStore**
> CollectionTranslationShow200Response collectionTranslationStore(storeCollectionTranslationRequest)


### Example

```typescript
import {
    CollectionTranslationApi,
    Configuration,
    StoreCollectionTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionTranslationApi(configuration);

let storeCollectionTranslationRequest: StoreCollectionTranslationRequest; //

const { status, data } = await apiInstance.collectionTranslationStore(
    storeCollectionTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeCollectionTranslationRequest** | **StoreCollectionTranslationRequest**|  | |


### Return type

**CollectionTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionTranslationUpdate**
> CollectionTranslationShow200Response collectionTranslationUpdate()


### Example

```typescript
import {
    CollectionTranslationApi,
    Configuration,
    UpdateCollectionTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionTranslationApi(configuration);

let collectionTranslation: string; //The collection translation ID (default to undefined)
let updateCollectionTranslationRequest: UpdateCollectionTranslationRequest; // (optional)

const { status, data } = await apiInstance.collectionTranslationUpdate(
    collectionTranslation,
    updateCollectionTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCollectionTranslationRequest** | **UpdateCollectionTranslationRequest**|  | |
| **collectionTranslation** | [**string**] | The collection translation ID | defaults to undefined|


### Return type

**CollectionTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionTranslationUpdate2**
> CollectionTranslationShow200Response collectionTranslationUpdate2()


### Example

```typescript
import {
    CollectionTranslationApi,
    Configuration,
    UpdateCollectionTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionTranslationApi(configuration);

let collectionTranslation: string; //The collection translation ID (default to undefined)
let updateCollectionTranslationRequest: UpdateCollectionTranslationRequest; // (optional)

const { status, data } = await apiInstance.collectionTranslationUpdate2(
    collectionTranslation,
    updateCollectionTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCollectionTranslationRequest** | **UpdateCollectionTranslationRequest**|  | |
| **collectionTranslation** | [**string**] | The collection translation ID | defaults to undefined|


### Return type

**CollectionTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

