# CountryApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**countryDestroy**](#countrydestroy) | **DELETE** /country/{country} | Remove the specified resource from storage|
|[**countryIndex**](#countryindex) | **GET** /country | Display a listing of the resource|
|[**countryShow**](#countryshow) | **GET** /country/{country} | Display the specified resource|
|[**countryStore**](#countrystore) | **POST** /country | Store a newly created resource in storage|
|[**countryUpdate**](#countryupdate) | **PATCH** /country/{country} | Update the specified resource in storage|
|[**countryUpdate2**](#countryupdate2) | **PUT** /country/{country} | Update the specified resource in storage|

# **countryDestroy**
> countryDestroy()


### Example

```typescript
import {
    CountryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)

const { status, data } = await apiInstance.countryDestroy(
    country
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **country** | [**string**] | The country ID | defaults to undefined|


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

# **countryIndex**
> CountryIndex200Response countryIndex()


### Example

```typescript
import {
    CountryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `items`, `partners`, `translations`. (optional) (default to undefined)

const { status, data } = await apiInstance.countryIndex(
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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;items&#x60;, &#x60;partners&#x60;, &#x60;translations&#x60;. | (optional) defaults to undefined|


### Return type

**CountryIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;CountryResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryShow**
> CountryShow200Response countryShow()


### Example

```typescript
import {
    CountryApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `items`, `partners`, `translations`. (optional) (default to undefined)

const { status, data } = await apiInstance.countryShow(
    country,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **country** | [**string**] | The country ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;items&#x60;, &#x60;partners&#x60;, &#x60;translations&#x60;. | (optional) defaults to undefined|


### Return type

**CountryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryStore**
> CountryShow200Response countryStore(storeCountryRequest)


### Example

```typescript
import {
    CountryApi,
    Configuration,
    StoreCountryRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let storeCountryRequest: StoreCountryRequest; //

const { status, data } = await apiInstance.countryStore(
    storeCountryRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeCountryRequest** | **StoreCountryRequest**|  | |


### Return type

**CountryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryUpdate**
> CountryShow200Response countryUpdate(updateCountryRequest)


### Example

```typescript
import {
    CountryApi,
    Configuration,
    UpdateCountryRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)
let updateCountryRequest: UpdateCountryRequest; //

const { status, data } = await apiInstance.countryUpdate(
    country,
    updateCountryRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCountryRequest** | **UpdateCountryRequest**|  | |
| **country** | [**string**] | The country ID | defaults to undefined|


### Return type

**CountryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryUpdate2**
> CountryShow200Response countryUpdate2(updateCountryRequest)


### Example

```typescript
import {
    CountryApi,
    Configuration,
    UpdateCountryRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryApi(configuration);

let country: string; //The country ID (default to undefined)
let updateCountryRequest: UpdateCountryRequest; //

const { status, data } = await apiInstance.countryUpdate2(
    country,
    updateCountryRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCountryRequest** | **UpdateCountryRequest**|  | |
| **country** | [**string**] | The country ID | defaults to undefined|


### Return type

**CountryShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

