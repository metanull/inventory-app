# ContactTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**contactTranslationDestroy**](#contacttranslationdestroy) | **DELETE** /contact-translation/{contactTranslation} | Remove the specified resource from storage|
|[**contactTranslationIndex**](#contacttranslationindex) | **GET** /contact-translation | Display a listing of the resource|
|[**contactTranslationShow**](#contacttranslationshow) | **GET** /contact-translation/{contactTranslation} | Display the specified resource|
|[**contactTranslationStore**](#contacttranslationstore) | **POST** /contact-translation | Store a newly created resource in storage|
|[**contactTranslationUpdate**](#contacttranslationupdate) | **PATCH** /contact-translation/{contactTranslation} | Update the specified resource in storage|
|[**contactTranslationUpdate2**](#contacttranslationupdate2) | **PUT** /contact-translation/{contactTranslation} | Update the specified resource in storage|

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
> ContactTranslationShow200Response contactTranslationShow()


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

**ContactTranslationShow200Response**

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
> ContactTranslationShow200Response contactTranslationStore(storeContactTranslationRequest)


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration,
    StoreContactTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let storeContactTranslationRequest: StoreContactTranslationRequest; //

const { status, data } = await apiInstance.contactTranslationStore(
    storeContactTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeContactTranslationRequest** | **StoreContactTranslationRequest**|  | |


### Return type

**ContactTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactTranslationUpdate**
> ContactTranslationShow200Response contactTranslationUpdate()


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration,
    UpdateContactTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let contactTranslation: string; //The contact translation ID (default to undefined)
let updateContactTranslationRequest: UpdateContactTranslationRequest; // (optional)

const { status, data } = await apiInstance.contactTranslationUpdate(
    contactTranslation,
    updateContactTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateContactTranslationRequest** | **UpdateContactTranslationRequest**|  | |
| **contactTranslation** | [**string**] | The contact translation ID | defaults to undefined|


### Return type

**ContactTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactTranslationUpdate2**
> ContactTranslationShow200Response contactTranslationUpdate2()


### Example

```typescript
import {
    ContactTranslationApi,
    Configuration,
    UpdateContactTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactTranslationApi(configuration);

let contactTranslation: string; //The contact translation ID (default to undefined)
let updateContactTranslationRequest: UpdateContactTranslationRequest; // (optional)

const { status, data } = await apiInstance.contactTranslationUpdate2(
    contactTranslation,
    updateContactTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateContactTranslationRequest** | **UpdateContactTranslationRequest**|  | |
| **contactTranslation** | [**string**] | The contact translation ID | defaults to undefined|


### Return type

**ContactTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

