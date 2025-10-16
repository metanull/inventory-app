# ContactApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**contactDestroy**](#contactdestroy) | **DELETE** /contact/{contact} | Remove the specified contact|
|[**contactIndex**](#contactindex) | **GET** /contact | Display a listing of contacts|
|[**contactShow**](#contactshow) | **GET** /contact/{contact} | Display the specified contact|
|[**contactStore**](#contactstore) | **POST** /contact | Store a newly created contact|
|[**contactUpdate**](#contactupdate) | **PATCH** /contact/{contact} | Update the specified contact|
|[**contactUpdate2**](#contactupdate2) | **PUT** /contact/{contact} | Update the specified contact|

# **contactDestroy**
> contactDestroy()


### Example

```typescript
import {
    ContactApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)

const { status, data } = await apiInstance.contactDestroy(
    contact
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contact** | [**string**] | The contact ID | defaults to undefined|


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

# **contactIndex**
> ContactIndex200Response contactIndex()


### Example

```typescript
import {
    ContactApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.contactIndex(
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

**ContactIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ContactResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactShow**
> ContactShow200Response contactShow()


### Example

```typescript
import {
    ContactApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.contactShow(
    contact,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **contact** | [**string**] | The contact ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**ContactShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactStore**
> ContactShow200Response contactStore(storeContactRequest)


### Example

```typescript
import {
    ContactApi,
    Configuration,
    StoreContactRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let storeContactRequest: StoreContactRequest; //

const { status, data } = await apiInstance.contactStore(
    storeContactRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeContactRequest** | **StoreContactRequest**|  | |


### Return type

**ContactShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** | &#x60;ContactResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactUpdate**
> ContactShow200Response contactUpdate(updateContactRequest)


### Example

```typescript
import {
    ContactApi,
    Configuration,
    UpdateContactRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)
let updateContactRequest: UpdateContactRequest; //

const { status, data } = await apiInstance.contactUpdate(
    contact,
    updateContactRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateContactRequest** | **UpdateContactRequest**|  | |
| **contact** | [**string**] | The contact ID | defaults to undefined|


### Return type

**ContactShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **contactUpdate2**
> ContactShow200Response contactUpdate2(updateContactRequest)


### Example

```typescript
import {
    ContactApi,
    Configuration,
    UpdateContactRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)
let updateContactRequest: UpdateContactRequest; //

const { status, data } = await apiInstance.contactUpdate2(
    contact,
    updateContactRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateContactRequest** | **UpdateContactRequest**|  | |
| **contact** | [**string**] | The contact ID | defaults to undefined|


### Return type

**ContactShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ContactResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

