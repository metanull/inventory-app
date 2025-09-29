# ItemApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemByType**](#itembytype) | **GET** /item/type/{type} | Get items by type|
|[**itemChildren**](#itemchildren) | **GET** /item/children | Get child items (items with a parent)|
|[**itemDestroy**](#itemdestroy) | **DELETE** /item/{item} | Remove the specified resource from storage|
|[**itemForTag**](#itemfortag) | **GET** /item/for-tag/{tag} | Get items for a specific tag|
|[**itemIndex**](#itemindex) | **GET** /item | Display a listing of the resource|
|[**itemParents**](#itemparents) | **GET** /item/parents | Get parent items (items with no parent)|
|[**itemShow**](#itemshow) | **GET** /item/{item} | Display the specified resource|
|[**itemStore**](#itemstore) | **POST** /item | Store a newly created resource in storage|
|[**itemUpdate**](#itemupdate) | **PUT** /item/{item} | Update the specified resource in storage|
|[**itemUpdateTags**](#itemupdatetags) | **PATCH** /item/{item}/tags | Update tags for the specified item without modifying other item properties|
|[**itemWithAllTags**](#itemwithalltags) | **POST** /item/with-all-tags | Get items that have ALL of the specified tags (AND condition)|
|[**itemWithAnyTags**](#itemwithanytags) | **POST** /item/with-any-tags | Get items that have ANY of the specified tags (OR condition)|

# **itemByType**
> ItemForTag200Response itemByType()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let type: string; // (default to undefined)
let type2: 'object' | 'monument' | 'detail' | 'picture'; // (default to undefined)

const { status, data } = await apiInstance.itemByType(
    type,
    type2
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **type** | [**string**] |  | defaults to undefined|
| **type2** | [**&#39;object&#39; | &#39;monument&#39; | &#39;detail&#39; | &#39;picture&#39;**]**Array<&#39;object&#39; &#124; &#39;monument&#39; &#124; &#39;detail&#39; &#124; &#39;picture&#39;>** |  | defaults to undefined|


### Return type

**ItemForTag200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemChildren**
> ItemForTag200Response itemChildren()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

const { status, data } = await apiInstance.itemChildren();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ItemForTag200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemDestroy**
> itemDestroy()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)

const { status, data } = await apiInstance.itemDestroy(
    item
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **item** | [**string**] | The item ID | defaults to undefined|


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

# **itemForTag**
> ItemForTag200Response itemForTag()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let tag: string; //The tag ID (default to undefined)

const { status, data } = await apiInstance.itemForTag(
    tag
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tag** | [**string**] | The tag ID | defaults to undefined|


### Return type

**ItemForTag200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemIndex**
> ItemIndex200Response itemIndex()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemIndex(
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

**ItemIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ItemResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemParents**
> ItemForTag200Response itemParents()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

const { status, data } = await apiInstance.itemParents();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ItemForTag200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemShow**
> ItemStore200Response itemShow()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemShow(
    item,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **item** | [**string**] | The item ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemStore**
> ItemStore200Response itemStore(storeItemRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    StoreItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let storeItemRequest: StoreItemRequest; //

const { status, data } = await apiInstance.itemStore(
    storeItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeItemRequest** | **StoreItemRequest**|  | |


### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemUpdate**
> ItemStore200Response itemUpdate()


### Example

```typescript
import {
    ItemApi,
    Configuration,
    UpdateItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let updateItemRequest: UpdateItemRequest; // (optional)

const { status, data } = await apiInstance.itemUpdate(
    item,
    updateItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemRequest** | **UpdateItemRequest**|  | |
| **item** | [**string**] | The item ID | defaults to undefined|


### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemUpdateTags**
> ItemStore200Response itemUpdateTags()

This endpoint allows quick editing of tag associations by specifying which tags to attach or detach from the item. It provides fine-grained control over tag operations without requiring a full item update.

### Example

```typescript
import {
    ItemApi,
    Configuration,
    ItemUpdateTagsRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //- The item to update tags for (default to undefined)
let itemUpdateTagsRequest: ItemUpdateTagsRequest; // (optional)

const { status, data } = await apiInstance.itemUpdateTags(
    item,
    itemUpdateTagsRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemUpdateTagsRequest** | **ItemUpdateTagsRequest**|  | |
| **item** | [**string**] | - The item to update tags for | defaults to undefined|


### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemWithAllTags**
> ItemForTag200Response itemWithAllTags(itemWithAllTagsRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    ItemWithAllTagsRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let itemWithAllTagsRequest: ItemWithAllTagsRequest; //

const { status, data } = await apiInstance.itemWithAllTags(
    itemWithAllTagsRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemWithAllTagsRequest** | **ItemWithAllTagsRequest**|  | |


### Return type

**ItemForTag200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemWithAnyTags**
> ItemForTag200Response itemWithAnyTags(itemWithAllTagsRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    ItemWithAllTagsRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let itemWithAllTagsRequest: ItemWithAllTagsRequest; //

const { status, data } = await apiInstance.itemWithAnyTags(
    itemWithAllTagsRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemWithAllTagsRequest** | **ItemWithAllTagsRequest**|  | |


### Return type

**ItemForTag200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

