# ItemApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemAttachTag**](#itemattachtag) | **POST** /item/{item}/attach-tag | Attach a single tag to an item|
|[**itemAttachTags**](#itemattachtags) | **POST** /item/{item}/attach-tags | Attach multiple tags to an item|
|[**itemByType**](#itembytype) | **GET** /item/type/{type} | Get items by type|
|[**itemChildren**](#itemchildren) | **GET** /item/children | Get child items (items with a parent)|
|[**itemDestroy**](#itemdestroy) | **DELETE** /item/{item} | Remove the specified resource from storage|
|[**itemDetachTag**](#itemdetachtag) | **DELETE** /item/{item}/detach-tag | Detach a single tag from an item|
|[**itemDetachTags**](#itemdetachtags) | **DELETE** /item/{item}/detach-tags | Detach multiple tags from an item|
|[**itemForTag**](#itemfortag) | **GET** /item/for-tag/{tag} | Get items for a specific tag|
|[**itemIndex**](#itemindex) | **GET** /item | Display a listing of the resource|
|[**itemParents**](#itemparents) | **GET** /item/parents | Get parent items (items with no parent)|
|[**itemShow**](#itemshow) | **GET** /item/{item} | Display the specified resource|
|[**itemStore**](#itemstore) | **POST** /item | Store a newly created resource in storage|
|[**itemUpdate**](#itemupdate) | **PATCH** /item/{item} | Update the specified resource in storage|
|[**itemUpdate2**](#itemupdate2) | **PUT** /item/{item} | Update the specified resource in storage|
|[**itemUpdateTags**](#itemupdatetags) | **PATCH** /item/{item}/tags | Update the tags associated with an item. This endpoint handles attaching and/or detaching tags from an item using a single operation. Designed for granular tag management, allowing callers to perform specific tag attach/detach operations without requiring a full item update|
|[**itemWithAllTags**](#itemwithalltags) | **POST** /item/with-all-tags | Get items that have ALL of the specified tags (AND condition)|
|[**itemWithAnyTags**](#itemwithanytags) | **POST** /item/with-any-tags | Get items that have ANY of the specified tags (OR condition)|

# **itemAttachTag**
> ItemShow200Response itemAttachTag(attachTagItemRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    AttachTagItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let attachTagItemRequest: AttachTagItemRequest; //

const { status, data } = await apiInstance.itemAttachTag(
    item,
    attachTagItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachTagItemRequest** | **AttachTagItemRequest**|  | |
| **item** | [**string**] | The item ID | defaults to undefined|


### Return type

**ItemShow200Response**

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

# **itemAttachTags**
> ItemShow200Response itemAttachTags(attachTagsItemRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    AttachTagsItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let attachTagsItemRequest: AttachTagsItemRequest; //

const { status, data } = await apiInstance.itemAttachTags(
    item,
    attachTagsItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachTagsItemRequest** | **AttachTagsItemRequest**|  | |
| **item** | [**string**] | The item ID | defaults to undefined|


### Return type

**ItemShow200Response**

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
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemByType(
    type,
    type2,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **type** | [**string**] |  | defaults to undefined|
| **type2** | [**&#39;object&#39; | &#39;monument&#39; | &#39;detail&#39; | &#39;picture&#39;**]**Array<&#39;object&#39; &#124; &#39;monument&#39; &#124; &#39;detail&#39; &#124; &#39;picture&#39;>** |  | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


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
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

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

let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemChildren(
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **include** | [**string**] |  | (optional) defaults to undefined|


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
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

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

# **itemDetachTag**
> ItemShow200Response itemDetachTag()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let tagId: string; // (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemDetachTag(
    item,
    tagId,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **item** | [**string**] | The item ID | defaults to undefined|
| **tagId** | [**string**] |  | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ItemShow200Response**

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

# **itemDetachTags**
> ItemShow200Response itemDetachTags()


### Example

```typescript
import {
    ItemApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let tagIds: Array<string>; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemDetachTags(
    item,
    tagIds,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **item** | [**string**] | The item ID | defaults to undefined|
| **tagIds** | **Array&lt;string&gt;** |  | (optional) defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ItemShow200Response**

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
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemForTag(
    tag,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tag** | [**string**] | The tag ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


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
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

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

let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemParents(
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **include** | [**string**] |  | (optional) defaults to undefined|


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
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemShow**
> ItemShow200Response itemShow()


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

**ItemShow200Response**

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
> ItemShow200Response itemStore(storeItemRequest)


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

**ItemShow200Response**

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
> ItemShow200Response itemUpdate()


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

**ItemShow200Response**

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

# **itemUpdate2**
> ItemShow200Response itemUpdate2()


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

const { status, data } = await apiInstance.itemUpdate2(
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

**ItemShow200Response**

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
> ItemShow200Response itemUpdateTags()


### Example

```typescript
import {
    ItemApi,
    Configuration,
    UpdateTagsItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //- The item to update tags for (default to undefined)
let updateTagsItemRequest: UpdateTagsItemRequest; // (optional)

const { status, data } = await apiInstance.itemUpdateTags(
    item,
    updateTagsItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateTagsItemRequest** | **UpdateTagsItemRequest**|  | |
| **item** | [**string**] | - The item to update tags for | defaults to undefined|


### Return type

**ItemShow200Response**

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

# **itemWithAllTags**
> ItemForTag200Response itemWithAllTags(withAllTagsItemRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    WithAllTagsItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let withAllTagsItemRequest: WithAllTagsItemRequest; //

const { status, data } = await apiInstance.itemWithAllTags(
    withAllTagsItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **withAllTagsItemRequest** | **WithAllTagsItemRequest**|  | |


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
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemWithAnyTags**
> ItemForTag200Response itemWithAnyTags(withAnyTagsItemRequest)


### Example

```typescript
import {
    ItemApi,
    Configuration,
    WithAnyTagsItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let withAnyTagsItemRequest: WithAnyTagsItemRequest; //

const { status, data } = await apiInstance.itemWithAnyTags(
    withAnyTagsItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **withAnyTagsItemRequest** | **WithAnyTagsItemRequest**|  | |


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
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

