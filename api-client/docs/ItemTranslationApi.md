# ItemTranslationApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemTranslationDestroy**](#itemtranslationdestroy) | **DELETE** /item-translation/{itemTranslation} | Remove the specified item translation|
|[**itemTranslationIndex**](#itemtranslationindex) | **GET** /item-translation | Display a listing of item translations|
|[**itemTranslationShow**](#itemtranslationshow) | **GET** /item-translation/{itemTranslation} | Display the specified item translation|
|[**itemTranslationStore**](#itemtranslationstore) | **POST** /item-translation | Store a newly created item translation|
|[**itemTranslationUpdate**](#itemtranslationupdate) | **PUT** /item-translation/{itemTranslation} | Update the specified item translation|

# **itemTranslationDestroy**
> number itemTranslationDestroy()


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

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationIndex**
> Array<ItemTranslationResource> itemTranslationIndex()


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

**Array<ItemTranslationResource>**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
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

const { status, data } = await apiInstance.itemTranslationShow(
    itemTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemTranslation** | [**string**] | The item translation ID | defaults to undefined|


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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationStore**
> number itemTranslationStore(itemTranslationStoreRequest)


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration,
    ItemTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let itemTranslationStoreRequest: ItemTranslationStoreRequest; //

const { status, data } = await apiInstance.itemTranslationStore(
    itemTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemTranslationStoreRequest** | **ItemTranslationStoreRequest**|  | |


### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemTranslationUpdate**
> ItemTranslationShow200Response itemTranslationUpdate()


### Example

```typescript
import {
    ItemTranslationApi,
    Configuration,
    ItemTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemTranslationApi(configuration);

let itemTranslation: string; //The item translation ID (default to undefined)
let itemTranslationUpdateRequest: ItemTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.itemTranslationUpdate(
    itemTranslation,
    itemTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemTranslationUpdateRequest** | **ItemTranslationUpdateRequest**|  | |
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
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

