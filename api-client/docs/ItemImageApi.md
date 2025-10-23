# ItemImageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**itemAttachImage**](#itemattachimage) | **POST** /item/{item}/attach-image | Attach an available image to an item|
|[**itemImageDestroy**](#itemimagedestroy) | **DELETE** /item-image/{itemImage} | Remove the specified item image|
|[**itemImageDetach**](#itemimagedetach) | **POST** /item-image/{itemImage}/detach | Detach an item image and convert it back to available image|
|[**itemImageDownload**](#itemimagedownload) | **GET** /item-image/{itemImage}/download | Returns the file to the caller|
|[**itemImageMoveDown**](#itemimagemovedown) | **PATCH** /item-image/{itemImage}/move-down | Move item image down in display order|
|[**itemImageMoveUp**](#itemimagemoveup) | **PATCH** /item-image/{itemImage}/move-up | Move item image up in display order|
|[**itemImageShow**](#itemimageshow) | **GET** /item-image/{itemImage} | Display the specified item image|
|[**itemImageTightenOrdering**](#itemimagetightenordering) | **PATCH** /item-image/{itemImage}/tighten-ordering | Tighten ordering for all images of the item|
|[**itemImageUpdate**](#itemimageupdate) | **PATCH** /item-image/{itemImage} | Update the specified item image|
|[**itemImageUpdate2**](#itemimageupdate2) | **PUT** /item-image/{itemImage} | Update the specified item image|
|[**itemImageView**](#itemimageview) | **GET** /item-image/{itemImage}/view | Returns the image file for direct viewing (e.g., for use in &lt;img&gt; src attribute)|
|[**itemImagesIndex**](#itemimagesindex) | **GET** /item/{item}/images | Display a listing of item images for a specific item|
|[**itemImagesStore**](#itemimagesstore) | **POST** /item/{item}/images | Store a newly created item image|

# **itemAttachImage**
> ItemImagesStore200Response itemAttachImage(attachFromAvailableItemImageRequest)


### Example

```typescript
import {
    ItemImageApi,
    Configuration,
    AttachFromAvailableItemImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let item: string; //The item ID (default to undefined)
let attachFromAvailableItemImageRequest: AttachFromAvailableItemImageRequest; //

const { status, data } = await apiInstance.itemAttachImage(
    item,
    attachFromAvailableItemImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachFromAvailableItemImageRequest** | **AttachFromAvailableItemImageRequest**|  | |
| **item** | [**string**] | The item ID | defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageDestroy**
> itemImageDestroy()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageDestroy(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


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

# **itemImageDetach**
> CollectionImageTightenOrdering200Response itemImageDetach()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageDetach(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**CollectionImageTightenOrdering200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;OperationSuccessResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageDownload**
> object itemImageDownload()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageDownload(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**object**

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

# **itemImageMoveDown**
> ItemImagesStore200Response itemImageMoveDown()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageMoveDown(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageMoveUp**
> ItemImagesStore200Response itemImageMoveUp()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageMoveUp(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageShow**
> ItemImagesStore200Response itemImageShow()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemImageShow(
    itemImage,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageTightenOrdering**
> CollectionImageTightenOrdering200Response itemImageTightenOrdering()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageTightenOrdering(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**CollectionImageTightenOrdering200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;OperationSuccessResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageUpdate**
> ItemImagesStore200Response itemImageUpdate()


### Example

```typescript
import {
    ItemImageApi,
    Configuration,
    UpdateItemImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)
let updateItemImageRequest: UpdateItemImageRequest; // (optional)

const { status, data } = await apiInstance.itemImageUpdate(
    itemImage,
    updateItemImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemImageRequest** | **UpdateItemImageRequest**|  | |
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageUpdate2**
> ItemImagesStore200Response itemImageUpdate2()


### Example

```typescript
import {
    ItemImageApi,
    Configuration,
    UpdateItemImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)
let updateItemImageRequest: UpdateItemImageRequest; // (optional)

const { status, data } = await apiInstance.itemImageUpdate2(
    itemImage,
    updateItemImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateItemImageRequest** | **UpdateItemImageRequest**|  | |
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImageView**
> object itemImageView()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let itemImage: string; //The item image ID (default to undefined)

const { status, data } = await apiInstance.itemImageView(
    itemImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **itemImage** | [**string**] | The item image ID | defaults to undefined|


### Return type

**object**

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

# **itemImagesIndex**
> ItemImagesIndex200Response itemImagesIndex()


### Example

```typescript
import {
    ItemImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let item: string; //The item ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.itemImagesIndex(
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

**ItemImagesIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **itemImagesStore**
> ItemImagesStore200Response itemImagesStore(storeItemImageRequest)


### Example

```typescript
import {
    ItemImageApi,
    Configuration,
    StoreItemImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ItemImageApi(configuration);

let item: string; //The item ID (default to undefined)
let storeItemImageRequest: StoreItemImageRequest; //

const { status, data } = await apiInstance.itemImagesStore(
    item,
    storeItemImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeItemImageRequest** | **StoreItemImageRequest**|  | |
| **item** | [**string**] | The item ID | defaults to undefined|


### Return type

**ItemImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ItemImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

