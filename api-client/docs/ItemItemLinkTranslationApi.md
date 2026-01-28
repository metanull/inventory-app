# ItemItemLinkTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemItemLinkTranslationDestroy**](#itemitemlinktranslationdestroy) | **DELETE** /item-item-link-translation/{itemItemLinkTranslation} | Remove the specified item-item link translation|
|[**itemItemLinkTranslationIndex**](#itemitemlinktranslationindex) | **GET** /item-item-link-translation | Display a listing of item-item link translations|
|[**itemItemLinkTranslationShow**](#itemitemlinktranslationshow) | **GET** /item-item-link-translation/{itemItemLinkTranslation} | Display the specified item-item link translation|
|[**itemItemLinkTranslationStore**](#itemitemlinktranslationstore) | **POST** /item-item-link-translation | Store a newly created item-item link translation|
|[**itemItemLinkTranslationUpdate**](#itemitemlinktranslationupdate) | **PATCH** /item-item-link-translation/{itemItemLinkTranslation} | Update the specified item-item link translation|
|[**itemItemLinkTranslationUpdate2**](#itemitemlinktranslationupdate2) | **PUT** /item-item-link-translation/{itemItemLinkTranslation} | Update the specified item-item link translation|

# **itemItemLinkTranslationDestroy**
> itemItemLinkTranslationDestroy()


### Example

```typescript
import {
    ItemItemLinkTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkTranslationApi(configuration);

let itemItemLinkTranslation: string; //The item item link translation ID (default to undefined)

const { status, data } = await apiInstance.itemItemLinkTranslationDestroy(
    itemItemLinkTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemItemLinkTranslation** | [**string**] | The item item link translation ID | defaults to undefined|


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

# **itemItemLinkTranslationIndex**
> ItemItemLinkTranslationIndex200Response itemItemLinkTranslationIndex()


### Example

```typescript
import {
    ItemItemLinkTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let itemItemLinkId: string; // (optional) (default to undefined)
let languageId: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemItemLinkTranslationIndex(
    page,
    perPage,
    itemItemLinkId,
    languageId
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **page** | [**number**] |  | (optional) defaults to undefined|
| **perPage** | [**number**] |  | (optional) defaults to undefined|
| **itemItemLinkId** | [**string**] |  | (optional) defaults to undefined|
| **languageId** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ItemItemLinkTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ItemItemLinkTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkTranslationShow**
> ItemItemLinkTranslationShow200Response itemItemLinkTranslationShow()


### Example

```typescript
import {
    ItemItemLinkTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkTranslationApi(configuration);

let itemItemLinkTranslation: string; //The item item link translation ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `itemItemLink`, `language`. (optional) (default to undefined)

const { status, data } = await apiInstance.itemItemLinkTranslationShow(
    itemItemLinkTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemItemLinkTranslation** | [**string**] | The item item link translation ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;itemItemLink&#x60;, &#x60;language&#x60;. | (optional) defaults to undefined|


### Return type

**ItemItemLinkTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkTranslationStore**
> ItemItemLinkTranslationShow200Response itemItemLinkTranslationStore(storeItemItemLinkTranslationRequest)


### Example

```typescript
import {
    ItemItemLinkTranslationApi,
    Configuration,
    StoreItemItemLinkTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkTranslationApi(configuration);

let storeItemItemLinkTranslationRequest: StoreItemItemLinkTranslationRequest; //

const { status, data } = await apiInstance.itemItemLinkTranslationStore(
    storeItemItemLinkTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeItemItemLinkTranslationRequest** | **StoreItemItemLinkTranslationRequest**|  | |


### Return type

**ItemItemLinkTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkTranslationUpdate**
> ItemItemLinkTranslationShow200Response itemItemLinkTranslationUpdate(updateItemItemLinkTranslationRequest)


### Example

```typescript
import {
    ItemItemLinkTranslationApi,
    Configuration,
    UpdateItemItemLinkTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkTranslationApi(configuration);

let itemItemLinkTranslation: string; //The item item link translation ID (default to undefined)
let updateItemItemLinkTranslationRequest: UpdateItemItemLinkTranslationRequest; //

const { status, data } = await apiInstance.itemItemLinkTranslationUpdate(
    itemItemLinkTranslation,
    updateItemItemLinkTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemItemLinkTranslationRequest** | **UpdateItemItemLinkTranslationRequest**|  | |
| **itemItemLinkTranslation** | [**string**] | The item item link translation ID | defaults to undefined|


### Return type

**ItemItemLinkTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkTranslationUpdate2**
> ItemItemLinkTranslationShow200Response itemItemLinkTranslationUpdate2(updateItemItemLinkTranslationRequest)


### Example

```typescript
import {
    ItemItemLinkTranslationApi,
    Configuration,
    UpdateItemItemLinkTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkTranslationApi(configuration);

let itemItemLinkTranslation: string; //The item item link translation ID (default to undefined)
let updateItemItemLinkTranslationRequest: UpdateItemItemLinkTranslationRequest; //

const { status, data } = await apiInstance.itemItemLinkTranslationUpdate2(
    itemItemLinkTranslation,
    updateItemItemLinkTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemItemLinkTranslationRequest** | **UpdateItemItemLinkTranslationRequest**|  | |
| **itemItemLinkTranslation** | [**string**] | The item item link translation ID | defaults to undefined|


### Return type

**ItemItemLinkTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

