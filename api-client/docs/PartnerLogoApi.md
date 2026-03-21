# PartnerLogoApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**partnerLogoDestroy**](#partnerlogodestroy) | **DELETE** /partner-logo/{partnerLogo} | Remove the specified resource from storage|
|[**partnerLogoDownload**](#partnerlogodownload) | **GET** /partner-logo/{partnerLogo}/download | Returns the file to the caller|
|[**partnerLogoIndex**](#partnerlogoindex) | **GET** /partner-logo | Display a listing of the resource|
|[**partnerLogoMoveDown**](#partnerlogomovedown) | **PATCH** /partner-logo/{partnerLogo}/move-down | Move partner logo down in display order|
|[**partnerLogoMoveUp**](#partnerlogomoveup) | **PATCH** /partner-logo/{partnerLogo}/move-up | Move partner logo up in display order|
|[**partnerLogoShow**](#partnerlogoshow) | **GET** /partner-logo/{partnerLogo} | Display the specified resource|
|[**partnerLogoStore**](#partnerlogostore) | **POST** /partner-logo | Store a newly created resource in storage|
|[**partnerLogoTightenOrdering**](#partnerlogotightenordering) | **PATCH** /partner-logo/{partnerLogo}/tighten-ordering | Tighten ordering for all logos of the partner|
|[**partnerLogoUpdate**](#partnerlogoupdate) | **PATCH** /partner-logo/{partnerLogo} | Update the specified resource in storage|
|[**partnerLogoUpdate2**](#partnerlogoupdate2) | **PUT** /partner-logo/{partnerLogo} | Update the specified resource in storage|
|[**partnerLogoView**](#partnerlogoview) | **GET** /partner-logo/{partnerLogo}/view | Returns the logo file for direct viewing (e.g., for use in &lt;img&gt; src attribute)|

# **partnerLogoDestroy**
> partnerLogoDestroy()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)

const { status, data } = await apiInstance.partnerLogoDestroy(
    partnerLogo
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


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

# **partnerLogoDownload**
> object partnerLogoDownload()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)

const { status, data } = await apiInstance.partnerLogoDownload(
    partnerLogo
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


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

# **partnerLogoIndex**
> PartnerLogoIndex200Response partnerLogoIndex()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let include: string; //Comma-separated list of related resources to include. Valid values: `partner`. (optional) (default to undefined)
let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)

const { status, data } = await apiInstance.partnerLogoIndex(
    include,
    page,
    perPage
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;. | (optional) defaults to undefined|
| **page** | [**number**] |  | (optional) defaults to undefined|
| **perPage** | [**number**] |  | (optional) defaults to undefined|


### Return type

**PartnerLogoIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;PartnerLogoResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoMoveDown**
> PartnerLogoShow200Response partnerLogoMoveDown()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)

const { status, data } = await apiInstance.partnerLogoMoveDown(
    partnerLogo
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


### Return type

**PartnerLogoShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerLogoResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoMoveUp**
> PartnerLogoShow200Response partnerLogoMoveUp()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)

const { status, data } = await apiInstance.partnerLogoMoveUp(
    partnerLogo
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


### Return type

**PartnerLogoShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerLogoResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoShow**
> PartnerLogoShow200Response partnerLogoShow()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `partner`. (optional) (default to undefined)

const { status, data } = await apiInstance.partnerLogoShow(
    partnerLogo,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;. | (optional) defaults to undefined|


### Return type

**PartnerLogoShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerLogoResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoStore**
> PartnerLogoShow200Response partnerLogoStore(storePartnerLogoRequest)


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration,
    StorePartnerLogoRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let storePartnerLogoRequest: StorePartnerLogoRequest; //

const { status, data } = await apiInstance.partnerLogoStore(
    storePartnerLogoRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storePartnerLogoRequest** | **StorePartnerLogoRequest**|  | |


### Return type

**PartnerLogoShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerLogoResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoTightenOrdering**
> CollectionImageTightenOrdering200Response partnerLogoTightenOrdering()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)

const { status, data } = await apiInstance.partnerLogoTightenOrdering(
    partnerLogo
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


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

# **partnerLogoUpdate**
> PartnerLogoShow200Response partnerLogoUpdate(updatePartnerLogoRequest)


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration,
    UpdatePartnerLogoRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)
let updatePartnerLogoRequest: UpdatePartnerLogoRequest; //

const { status, data } = await apiInstance.partnerLogoUpdate(
    partnerLogo,
    updatePartnerLogoRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerLogoRequest** | **UpdatePartnerLogoRequest**|  | |
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


### Return type

**PartnerLogoShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerLogoResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoUpdate2**
> PartnerLogoShow200Response partnerLogoUpdate2(updatePartnerLogoRequest)


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration,
    UpdatePartnerLogoRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)
let updatePartnerLogoRequest: UpdatePartnerLogoRequest; //

const { status, data } = await apiInstance.partnerLogoUpdate2(
    partnerLogo,
    updatePartnerLogoRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updatePartnerLogoRequest** | **UpdatePartnerLogoRequest**|  | |
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


### Return type

**PartnerLogoShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;PartnerLogoResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **partnerLogoView**
> object partnerLogoView()


### Example

```typescript
import {
    PartnerLogoApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new PartnerLogoApi(configuration);

let partnerLogo: string; //The partner logo ID (default to undefined)

const { status, data } = await apiInstance.partnerLogoView(
    partnerLogo
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **partnerLogo** | [**string**] | The partner logo ID | defaults to undefined|


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

