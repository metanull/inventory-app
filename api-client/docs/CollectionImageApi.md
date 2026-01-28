# CollectionImageApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**collectionAttachImage**](#collectionattachimage) | **POST** /collection/{collection}/attach-image | Attach an available image to a collection|
|[**collectionImageDestroy**](#collectionimagedestroy) | **DELETE** /collection-image/{collectionImage} | Remove the specified collection image|
|[**collectionImageDetach**](#collectionimagedetach) | **POST** /collection-image/{collectionImage}/detach | Detach a collection image and convert it back to available image|
|[**collectionImageDownload**](#collectionimagedownload) | **GET** /collection-image/{collectionImage}/download | Returns the file to the caller|
|[**collectionImageMoveDown**](#collectionimagemovedown) | **PATCH** /collection-image/{collectionImage}/move-down | Move collection image down in display order|
|[**collectionImageMoveUp**](#collectionimagemoveup) | **PATCH** /collection-image/{collectionImage}/move-up | Move collection image up in display order|
|[**collectionImageShow**](#collectionimageshow) | **GET** /collection-image/{collectionImage} | Display the specified collection image|
|[**collectionImageTightenOrdering**](#collectionimagetightenordering) | **PATCH** /collection-image/{collectionImage}/tighten-ordering | Tighten ordering for all images of the collection|
|[**collectionImageUpdate**](#collectionimageupdate) | **PATCH** /collection-image/{collectionImage} | Update the specified collection image|
|[**collectionImageUpdate2**](#collectionimageupdate2) | **PUT** /collection-image/{collectionImage} | Update the specified collection image|
|[**collectionImageView**](#collectionimageview) | **GET** /collection-image/{collectionImage}/view | Returns the image file for direct viewing (e.g., for use in &lt;img&gt; src attribute)|
|[**collectionImagesIndex**](#collectionimagesindex) | **GET** /collection/{collection}/images | Display a listing of collection images for a specific collection|
|[**collectionImagesStore**](#collectionimagesstore) | **POST** /collection/{collection}/images | Store a newly created collection image|

# **collectionAttachImage**
> CollectionImagesStore200Response collectionAttachImage(attachFromAvailableCollectionImageRequest)


### Example

```typescript
import {
    CollectionImageApi,
    Configuration,
    AttachFromAvailableCollectionImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collection: string; //The collection ID (default to undefined)
let attachFromAvailableCollectionImageRequest: AttachFromAvailableCollectionImageRequest; //

const { status, data } = await apiInstance.collectionAttachImage(
    collection,
    attachFromAvailableCollectionImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **attachFromAvailableCollectionImageRequest** | **AttachFromAvailableCollectionImageRequest**|  | |
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImageDestroy**
> collectionImageDestroy()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageDestroy(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


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

# **collectionImageDetach**
> CollectionImageTightenOrdering200Response collectionImageDetach()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageDetach(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


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

# **collectionImageDownload**
> object collectionImageDownload()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageDownload(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


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

# **collectionImageMoveDown**
> CollectionImagesStore200Response collectionImageMoveDown()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageMoveDown(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImageMoveUp**
> CollectionImagesStore200Response collectionImageMoveUp()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageMoveUp(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImageShow**
> CollectionImagesStore200Response collectionImageShow()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `collection`. (optional) (default to undefined)

const { status, data } = await apiInstance.collectionImageShow(
    collectionImage,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;collection&#x60;. | (optional) defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImageTightenOrdering**
> CollectionImageTightenOrdering200Response collectionImageTightenOrdering()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageTightenOrdering(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


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

# **collectionImageUpdate**
> CollectionImagesStore200Response collectionImageUpdate()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration,
    UpdateCollectionImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)
let updateCollectionImageRequest: UpdateCollectionImageRequest; // (optional)

const { status, data } = await apiInstance.collectionImageUpdate(
    collectionImage,
    updateCollectionImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCollectionImageRequest** | **UpdateCollectionImageRequest**|  | |
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImageUpdate2**
> CollectionImagesStore200Response collectionImageUpdate2()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration,
    UpdateCollectionImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)
let updateCollectionImageRequest: UpdateCollectionImageRequest; // (optional)

const { status, data } = await apiInstance.collectionImageUpdate2(
    collectionImage,
    updateCollectionImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCollectionImageRequest** | **UpdateCollectionImageRequest**|  | |
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImageView**
> object collectionImageView()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collectionImage: string; //The collection image ID (default to undefined)

const { status, data } = await apiInstance.collectionImageView(
    collectionImage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collectionImage** | [**string**] | The collection image ID | defaults to undefined|


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

# **collectionImagesIndex**
> CollectionImagesIndex200Response collectionImagesIndex()


### Example

```typescript
import {
    CollectionImageApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collection: string; //The collection ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `collection`. (optional) (default to undefined)

const { status, data } = await apiInstance.collectionImagesIndex(
    collection,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **collection** | [**string**] | The collection ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;collection&#x60;. | (optional) defaults to undefined|


### Return type

**CollectionImagesIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **collectionImagesStore**
> CollectionImagesStore200Response collectionImagesStore(storeCollectionImageRequest)


### Example

```typescript
import {
    CollectionImageApi,
    Configuration,
    StoreCollectionImageRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CollectionImageApi(configuration);

let collection: string; //The collection ID (default to undefined)
let storeCollectionImageRequest: StoreCollectionImageRequest; //

const { status, data } = await apiInstance.collectionImagesStore(
    collection,
    storeCollectionImageRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeCollectionImageRequest** | **StoreCollectionImageRequest**|  | |
| **collection** | [**string**] | The collection ID | defaults to undefined|


### Return type

**CollectionImagesStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CollectionImageResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

