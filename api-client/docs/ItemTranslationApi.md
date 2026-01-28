# ItemTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemTranslationDestroy**](#itemtranslationdestroy) | **DELETE** /item-translation/{itemTranslation} | Remove the specified item translation|
|[**itemTranslationIndex**](#itemtranslationindex) | **GET** /item-translation | Display a listing of item translations|
|[**itemTranslationShow**](#itemtranslationshow) | **GET** /item-translation/{itemTranslation} | Display the specified item translation|
|[**itemTranslationStore**](#itemtranslationstore) | **POST** /item-translation | Store a newly created item translation|
|[**itemTranslationUpdate**](#itemtranslationupdate) | **PATCH** /item-translation/{itemTranslation} | Update the specified item translation|
|[**itemTranslationUpdate2**](#itemtranslationupdate2) | **PUT** /item-translation/{itemTranslation} | Update the specified item translation|

# **itemTranslationDestroy**
> itemTranslationDestroy()


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let itemTranslation: string; //The item translation ID (default to undefined)

const { status, data } = await apiInstance.itemTranslationDestroy(
    itemTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemTranslation** | [**string**] | The item translation ID | defaults to undefined|


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

# **itemTranslationIndex**
> ItemTranslationIndex200Response itemTranslationIndex()


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let itemId: string; // (optional) (default to undefined)
let languageId: string; // (optional) (default to undefined)
let contextId: string; // (optional) (default to undefined)
let defaultContext: boolean; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemTranslationIndex(
    page,
    perPage,
    itemId,
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
| **itemId** | [**string**] |  | (optional) defaults to undefined|
| **languageId** | [**string**] |  | (optional) defaults to undefined|
| **contextId** | [**string**] |  | (optional) defaults to undefined|
| **defaultContext** | [**boolean**] |  | (optional) defaults to undefined|


### Return type

**ItemTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ItemTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationShow**
> ItemTranslationShow200Response itemTranslationShow()


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let itemTranslation: string; //The item translation ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `item`, `language`, `context`, `author`, `textCopyEditor`, `translator`, `translationCopyEditor`. (optional) (default to undefined)

const { status, data } = await apiInstance.itemTranslationShow(
    itemTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemTranslation** | [**string**] | The item translation ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;item&#x60;, &#x60;language&#x60;, &#x60;context&#x60;, &#x60;author&#x60;, &#x60;textCopyEditor&#x60;, &#x60;translator&#x60;, &#x60;translationCopyEditor&#x60;. | (optional) defaults to undefined|


### Return type

**ItemTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationStore**
> ItemTranslationShow200Response itemTranslationStore(storeItemTranslationRequest)


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration,
    StoreItemTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let storeItemTranslationRequest: StoreItemTranslationRequest; //

const { status, data } = await apiInstance.itemTranslationStore(
    storeItemTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeItemTranslationRequest** | **StoreItemTranslationRequest**|  | |


### Return type

**ItemTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationUpdate**
> ItemTranslationShow200Response itemTranslationUpdate()


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration,
    UpdateItemTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let itemTranslation: string; //The item translation ID (default to undefined)
let updateItemTranslationRequest: UpdateItemTranslationRequest; // (optional)

const { status, data } = await apiInstance.itemTranslationUpdate(
    itemTranslation,
    updateItemTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemTranslationRequest** | **UpdateItemTranslationRequest**|  | |
| **itemTranslation** | [**string**] | The item translation ID | defaults to undefined|


### Return type

**ItemTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationUpdate2**
> ItemTranslationShow200Response itemTranslationUpdate2()


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration,
    UpdateItemTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let itemTranslation: string; //The item translation ID (default to undefined)
let updateItemTranslationRequest: UpdateItemTranslationRequest; // (optional)

const { status, data } = await apiInstance.itemTranslationUpdate2(
    itemTranslation,
    updateItemTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemTranslationRequest** | **UpdateItemTranslationRequest**|  | |
| **itemTranslation** | [**string**] | The item translation ID | defaults to undefined|


### Return type

**ItemTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

