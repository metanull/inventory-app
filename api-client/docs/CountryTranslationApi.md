# CountryTranslationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**countryTranslationDestroy**](#countrytranslationdestroy) | **DELETE** /country-translation/{countryTranslation} | Remove the specified resource from storage|
|[**countryTranslationIndex**](#countrytranslationindex) | **GET** /country-translation | Display a listing of the resource|
|[**countryTranslationShow**](#countrytranslationshow) | **GET** /country-translation/{countryTranslation} | Display the specified resource|
|[**countryTranslationStore**](#countrytranslationstore) | **POST** /country-translation | Store a newly created resource in storage|
|[**countryTranslationUpdate**](#countrytranslationupdate) | **PATCH** /country-translation/{countryTranslation} | Update the specified resource in storage|
|[**countryTranslationUpdate2**](#countrytranslationupdate2) | **PUT** /country-translation/{countryTranslation} | Update the specified resource in storage|

# **countryTranslationDestroy**
> countryTranslationDestroy()


### Example

```typescript
import {
    CountryTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryTranslationApi(configuration);

let countryTranslation: string; //The country translation ID (default to undefined)

const { status, data } = await apiInstance.countryTranslationDestroy(
    countryTranslation
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **countryTranslation** | [**string**] | The country translation ID | defaults to undefined|


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

# **countryTranslationIndex**
> CountryTranslationIndex200Response countryTranslationIndex()


### Example

```typescript
import {
    CountryTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryTranslationApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.countryTranslationIndex(
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

**CountryTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;CountryTranslationResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryTranslationShow**
> CountryTranslationShow200Response countryTranslationShow()


### Example

```typescript
import {
    CountryTranslationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryTranslationApi(configuration);

let countryTranslation: string; //The country translation ID (default to undefined)
let include: string; // (optional) (default to undefined)

const { status, data } = await apiInstance.countryTranslationShow(
    countryTranslation,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **countryTranslation** | [**string**] | The country translation ID | defaults to undefined|
| **include** | [**string**] |  | (optional) defaults to undefined|


### Return type

**CountryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryTranslationStore**
> CountryTranslationShow200Response countryTranslationStore(storeCountryTranslationRequest)


### Example

```typescript
import {
    CountryTranslationApi,
    Configuration,
    StoreCountryTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryTranslationApi(configuration);

let storeCountryTranslationRequest: StoreCountryTranslationRequest; //

const { status, data } = await apiInstance.countryTranslationStore(
    storeCountryTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeCountryTranslationRequest** | **StoreCountryTranslationRequest**|  | |


### Return type

**CountryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryTranslationResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryTranslationUpdate**
> CountryTranslationShow200Response countryTranslationUpdate()


### Example

```typescript
import {
    CountryTranslationApi,
    Configuration,
    UpdateCountryTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryTranslationApi(configuration);

let countryTranslation: string; //The country translation ID (default to undefined)
let updateCountryTranslationRequest: UpdateCountryTranslationRequest; // (optional)

const { status, data } = await apiInstance.countryTranslationUpdate(
    countryTranslation,
    updateCountryTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCountryTranslationRequest** | **UpdateCountryTranslationRequest**|  | |
| **countryTranslation** | [**string**] | The country translation ID | defaults to undefined|


### Return type

**CountryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **countryTranslationUpdate2**
> CountryTranslationShow200Response countryTranslationUpdate2()


### Example

```typescript
import {
    CountryTranslationApi,
    Configuration,
    UpdateCountryTranslationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new CountryTranslationApi(configuration);

let countryTranslation: string; //The country translation ID (default to undefined)
let updateCountryTranslationRequest: UpdateCountryTranslationRequest; // (optional)

const { status, data } = await apiInstance.countryTranslationUpdate2(
    countryTranslation,
    updateCountryTranslationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateCountryTranslationRequest** | **UpdateCountryTranslationRequest**|  | |
| **countryTranslation** | [**string**] | The country translation ID | defaults to undefined|


### Return type

**CountryTranslationShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;CountryTranslationResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

