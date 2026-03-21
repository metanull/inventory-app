# ItemItemLinkApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemItemLinkDestroy**](#itemitemlinkdestroy) | **DELETE** /item-item-link/{itemItemLink} | Remove the specified resource from storage|
|[**itemItemLinkIndex**](#itemitemlinkindex) | **GET** /item-item-link | Display a listing of the resource|
|[**itemItemLinkShow**](#itemitemlinkshow) | **GET** /item-item-link/{itemItemLink} | Display the specified resource|
|[**itemItemLinkStore**](#itemitemlinkstore) | **POST** /item-item-link | Store a newly created resource in storage|
|[**itemItemLinkUpdate**](#itemitemlinkupdate) | **PATCH** /item-item-link/{itemItemLink} | Update the specified resource in storage|
|[**itemItemLinkUpdate2**](#itemitemlinkupdate2) | **PUT** /item-item-link/{itemItemLink} | Update the specified resource in storage|

# **itemItemLinkDestroy**
> itemItemLinkDestroy()


### Example

```typescript
import {
    ItemItemLinkApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkApi(configuration);

let itemItemLink: string; // (default to undefined)

const { status, data } = await apiInstance.itemItemLinkDestroy(
    itemItemLink
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemItemLink** | [**string**] |  | defaults to undefined|


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
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkIndex**
> ItemItemLinkIndex200Response itemItemLinkIndex()


### Example

```typescript
import {
    ItemItemLinkApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let sourceId: string; // (optional) (default to undefined)
let targetId: string; // (optional) (default to undefined)
let contextId: string; // (optional) (default to undefined)
let itemId: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemItemLinkIndex(
    page,
    perPage,
    sourceId,
    targetId,
    contextId,
    itemId
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **page** | [**number**] |  | (optional) defaults to undefined|
| **perPage** | [**number**] |  | (optional) defaults to undefined|
| **sourceId** | [**string**] |  | (optional) defaults to undefined|
| **targetId** | [**string**] |  | (optional) defaults to undefined|
| **contextId** | [**string**] |  | (optional) defaults to undefined|
| **itemId** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ItemItemLinkIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ItemItemLinkResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkShow**
> ItemItemLinkShow200Response itemItemLinkShow()


### Example

```typescript
import {
    ItemItemLinkApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkApi(configuration);

let itemItemLink: string; // (default to undefined)

const { status, data } = await apiInstance.itemItemLinkShow(
    itemItemLink
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemItemLink** | [**string**] |  | defaults to undefined|


### Return type

**ItemItemLinkShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkStore**
> ItemItemLinkShow200Response itemItemLinkStore(storeItemItemLinkRequest)


### Example

```typescript
import {
    ItemItemLinkApi,
    Configuration,
    StoreItemItemLinkRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkApi(configuration);

let storeItemItemLinkRequest: StoreItemItemLinkRequest; //

const { status, data } = await apiInstance.itemItemLinkStore(
    storeItemItemLinkRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeItemItemLinkRequest** | **StoreItemItemLinkRequest**|  | |


### Return type

**ItemItemLinkShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkUpdate**
> ItemItemLinkShow200Response itemItemLinkUpdate(updateItemItemLinkRequest)


### Example

```typescript
import {
    ItemItemLinkApi,
    Configuration,
    UpdateItemItemLinkRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkApi(configuration);

let itemItemLink: string; // (default to undefined)
let updateItemItemLinkRequest: UpdateItemItemLinkRequest; //

const { status, data } = await apiInstance.itemItemLinkUpdate(
    itemItemLink,
    updateItemItemLinkRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemItemLinkRequest** | **UpdateItemItemLinkRequest**|  | |
| **itemItemLink** | [**string**] |  | defaults to undefined|


### Return type

**ItemItemLinkShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemItemLinkUpdate2**
> ItemItemLinkShow200Response itemItemLinkUpdate2(updateItemItemLinkRequest)


### Example

```typescript
import {
    ItemItemLinkApi,
    Configuration,
    UpdateItemItemLinkRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemItemLinkApi(configuration);

let itemItemLink: string; // (default to undefined)
let updateItemItemLinkRequest: UpdateItemItemLinkRequest; //

const { status, data } = await apiInstance.itemItemLinkUpdate2(
    itemItemLink,
    updateItemItemLinkRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemItemLinkRequest** | **UpdateItemItemLinkRequest**|  | |
| **itemItemLink** | [**string**] |  | defaults to undefined|


### Return type

**ItemItemLinkShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemItemLinkResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

