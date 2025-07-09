---
layout: default
title: "DetailTranslationsApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# DetailTranslationsApi

All URIs are relative to *http://localhost:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**detailTranslationDestroy**](#detailtranslationdestroy) | **DELETE** /detail-translation/{detailTranslation} | Remove the specified detail translation|
|[**detailTranslationIndex**](#detailtranslationindex) | **GET** /detail-translation | Display a listing of detail translations|
|[**detailTranslationShow**](#detailtranslationshow) | **GET** /detail-translation/{detailTranslation} | Display the specified detail translation|
|[**detailTranslationStore**](#detailtranslationstore) | **POST** /detail-translation | Store a newly created detail translation|
|[**detailTranslationUpdate**](#detailtranslationupdate) | **PUT** /detail-translation/{detailTranslation} | Update the specified detail translation|

# **detailTranslationDestroy**
> number detailTranslationDestroy()


### Example

```typescript
import {
    DetailTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailTranslationsApi(configuration);

let detailTranslation: string; //The detail translation ID (default to undefined)

const { status, data } = await apiInstance.detailTranslationDestroy(
    detailTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detailTranslation** | [**string**] | The detail translation ID | defaults to undefined|


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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **detailTranslationIndex**
> Array<DetailTranslationResource> detailTranslationIndex()


### Example

```typescript
import {
    DetailTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailTranslationsApi(configuration);

const { status, data } = await apiInstance.detailTranslationIndex();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**Array<DetailTranslationResource>**

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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **detailTranslationShow**
> DetailTranslationShow200Response detailTranslationShow()


### Example

```typescript
import {
    DetailTranslationsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailTranslationsApi(configuration);

let detailTranslation: string; //The detail translation ID (default to undefined)

const { status, data } = await apiInstance.detailTranslationShow(
    detailTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detailTranslation** | [**string**] | The detail translation ID | defaults to undefined|


### Return type

**DetailTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;DetailTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **detailTranslationStore**
> number detailTranslationStore(detailTranslationStoreRequest)


### Example

```typescript
import {
    DetailTranslationsApi,
    Configuration,
    DetailTranslationStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailTranslationsApi(configuration);

let detailTranslationStoreRequest: DetailTranslationStoreRequest; //

const { status, data } = await apiInstance.detailTranslationStore(
    detailTranslationStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detailTranslationStoreRequest** | **DetailTranslationStoreRequest**|  | |


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

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **detailTranslationUpdate**
> DetailTranslationShow200Response detailTranslationUpdate()


### Example

```typescript
import {
    DetailTranslationsApi,
    Configuration,
    DetailTranslationUpdateRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new DetailTranslationsApi(configuration);

let detailTranslation: string; //The detail translation ID (default to undefined)
let detailTranslationUpdateRequest: DetailTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.detailTranslationUpdate(
    detailTranslation,
    detailTranslationUpdateRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **detailTranslationUpdateRequest** | **DetailTranslationUpdateRequest**|  | |
| **detailTranslation** | [**string**] | The detail translation ID | defaults to undefined|


### Return type

**DetailTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;DetailTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)



---

*This documentation was automatically generated from the TypeScript API client.*
