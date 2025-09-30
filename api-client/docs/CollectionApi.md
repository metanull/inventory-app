# CollectionApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**collectionAttachItem**](#collectionattachitem) | **POST** /collection/{collection}/attach-item | Attach an item to a collection via many-to-many relationship|
|[**collectionAttachItems**](#collectionattachitems) | **POST** /collection/{collection}/attach-items | Attach multiple items to a collection|
|[**collectionByType**](#collectionbytype) | **GET** /collection/type/{type} | Get collections by type|
|[**collectionDestroy**](#collectiondestroy) | **DELETE** /collection/{collection} | Remove the specified collection from storage|
|[**collectionDetachItem**](#collectiondetachitem) | **DELETE** /collection/{collection}/detach-item | Detach an item from a collection|
|[**collectionDetachItems**](#collectiondetachitems) | **DELETE** /collection/{collection}/detach-items | Detach multiple items from a collection|
|[**collectionIndex**](#collectionindex) | **GET** /collection | Display a listing of the collections|
|[**collectionShow**](#collectionshow) | **GET** /collection/{collection} | Display the specified collection|
|[**collectionStore**](#collectionstore) | **POST** /collection | Store a newly created collection in storage|
|[**collectionUpdate**](#collectionupdate) | **PUT** /collection/{collection} | Update the specified collection in storage|

# **collectionAttachItem**
> CollectionAttachItem200Response collectionAttachItem(collectionAttachItemRequest)


### Example

```typescript
import {
    CollectionApi,
    Configuration,
    CollectionAttachItemRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let collectionAttachItemRequest: CollectionAttachItemRequest; //

const { status, data } = await apiInstance.collectionAttachItem(
    collection,
    collectionAttachItemRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionAttachItemRequest** | **CollectionAttachItemRequest**|  | |
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionAttachItem200Response**

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
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionAttachItems**
> CollectionAttachItems200Response collectionAttachItems(collectionAttachItemsRequest)


### Example

```typescript
import {
    CollectionApi,
    Configuration,
    CollectionAttachItemsRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let collectionAttachItemsRequest: CollectionAttachItemsRequest; //

const { status, data } = await apiInstance.collectionAttachItems(
    collection,
    collectionAttachItemsRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionAttachItemsRequest** | **CollectionAttachItemsRequest**|  | |
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionAttachItems200Response**

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
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionByType**
> CollectionByType200Response collectionByType()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let type: string; // (default to undefined)
let type2: 'collection' | 'exhibition' | 'gallery'; // (default to undefined)

const { status, data } = await apiInstance.collectionByType(
    type,
    type2
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **type** | [**string**] |  | defaults to undefined|
| **type2** | [**&#39;collection&#39; | &#39;exhibition&#39; | &#39;gallery&#39;**]**Array<&#39;collection&#39; &#124; &#39;exhibition&#39; &#124; &#39;gallery&#39;>** |  | defaults to undefined|


### Return type

**CollectionByType200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;CollectionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionDestroy**
> collectionDestroy()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)

const { status, data } = await apiInstance.collectionDestroy(
    collection
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|


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

# **collectionDetachItem**
> CollectionDetachItem200Response collectionDetachItem()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let itemId: string; // (default to undefined)

const { status, data } = await apiInstance.collectionDetachItem(
    collection,
    itemId
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|
| **itemId** | [**string**] |  | defaults to undefined|


### Return type

**CollectionDetachItem200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionDetachItems**
> CollectionDetachItems200Response collectionDetachItems()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let itemIds: Array<string>; // (default to undefined)

const { status, data } = await apiInstance.collectionDetachItems(
    collection,
    itemIds
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|
| **itemIds** | **Array&lt;string&gt;** |  | defaults to undefined|


### Return type

**CollectionDetachItems200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionIndex**
> CollectionIndex200Response collectionIndex()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.collectionIndex(
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

**CollectionIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;CollectionResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionShow**
> CollectionStore200Response collectionShow()


### Example

```typescript
import {
    CollectionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.collectionShow(
    collection,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**CollectionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionStore**
> CollectionStore200Response collectionStore(collectionStoreRequest)


### Example

```typescript
import {
    CollectionApi,
    Configuration,
    CollectionStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collectionStoreRequest: CollectionStoreRequest; //

const { status, data } = await apiInstance.collectionStore(
    collectionStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionStoreRequest** | **CollectionStoreRequest**|  | |


### Return type

**CollectionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionUpdate**
> CollectionStore200Response collectionUpdate()


### Example

```typescript
import {
    CollectionApi,
    Configuration,
    CollectionUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionApi(configuration);

let collection: string; //The collection ID (default to undefined)
let collectionUpdateRequest: CollectionUpdateRequest; // (optional)

const { status, data } = await apiInstance.collectionUpdate(
    collection,
    collectionUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionUpdateRequest** | **CollectionUpdateRequest**|  | |
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

