# ContactTranslationApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**contactTranslationDestroy**](#contacttranslationdestroy) | **DELETE** /contact-translation/{contactTranslation} | Remove the specified resource from storage|
|[**contactTranslationIndex**](#contacttranslationindex) | **GET** /contact-translation | Display a listing of the resource|
|[**contactTranslationShow**](#contacttranslationshow) | **GET** /contact-translation/{contactTranslation} | Display the specified resource|
|[**contactTranslationStore**](#contacttranslationstore) | **POST** /contact-translation | Store a newly created resource in storage|
|[**contactTranslationUpdate**](#contacttranslationupdate) | **PUT** /contact-translation/{contactTranslation} | Update the specified resource in storage|

# **contactTranslationDestroy**
> contactTranslationDestroy()


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let contactTranslation: string; //The contact translation ID (default to undefined)

const { status, data } = await apiInstance.contactTranslationDestroy(
    contactTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contactTranslation** | [**string**] | The contact translation ID | defaults to undefined|


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

# **contactTranslationIndex**
> ContactTranslationIndex200Response contactTranslationIndex()


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

const { status, data } = await apiInstance.contactTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ContactTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ContactTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactTranslationShow**
> ContactTranslationStore200Response contactTranslationShow()


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let contactTranslation: string; //The contact translation ID (default to undefined)

const { status, data } = await apiInstance.contactTranslationShow(
    contactTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contactTranslation** | [**string**] | The contact translation ID | defaults to undefined|


### Return type

**ContactTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactTranslationStore**
> ContactTranslationStore200Response contactTranslationStore(contactTranslationStoreRequest)


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration,
    ContactTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let contactTranslationStoreRequest: ContactTranslationStoreRequest; //

const { status, data } = await apiInstance.contactTranslationStore(
    contactTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contactTranslationStoreRequest** | **ContactTranslationStoreRequest**|  | |


### Return type

**ContactTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactTranslationUpdate**
> ContactTranslationStore200Response contactTranslationUpdate()


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration,
    ContactTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let contactTranslation: string; //The contact translation ID (default to undefined)
let contactTranslationUpdateRequest: ContactTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.contactTranslationUpdate(
    contactTranslation,
    contactTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contactTranslationUpdateRequest** | **ContactTranslationUpdateRequest**|  | |
| **contactTranslation** | [**string**] | The contact translation ID | defaults to undefined|


### Return type

**ContactTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

