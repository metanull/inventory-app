# ProjectApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**projectDestroy**](#projectdestroy) | **DELETE** /project/{project} | Remove the specified resource from storage|
|[**projectEnabled**](#projectenabled) | **GET** /project/enabled | Get all visible projects. The project becomes \&quot;visible\&quot; when all conditions are matched: - is_enabled is true - is_launched is true - current date &gt;&#x3D; launch_date|
|[**projectIndex**](#projectindex) | **GET** /project | Display a listing of the resource|
|[**projectSetEnabled**](#projectsetenabled) | **PATCH** /project/{project}/enabled | Toggle Enable/disable on a project|
|[**projectSetLaunched**](#projectsetlaunched) | **PATCH** /project/{project}/launched | Toggle Launched/not-launched on a project. Important: It is independant from the &#x60;launch_date&#x60; value. It is an idicator showing that the project is to be considered \&#39;laucnhed\&#39; as soon as the launch date it reached|
|[**projectShow**](#projectshow) | **GET** /project/{project} | Display the specified resource|
|[**projectStore**](#projectstore) | **POST** /project | Store a newly created resource in storage|
|[**projectUpdate**](#projectupdate) | **PATCH** /project/{project} | Update the specified resource in storage|
|[**projectUpdate2**](#projectupdate2) | **PUT** /project/{project} | Update the specified resource in storage|

# **projectDestroy**
> projectDestroy()


### Example

```typescript
import {
    ProjectApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)

const { status, data } = await apiInstance.projectDestroy(
    project
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **project** | [**string**] | The project ID | defaults to undefined|


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

# **projectEnabled**
> ProjectEnabled200Response projectEnabled()


### Example

```typescript
import {
    ProjectApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

const { status, data } = await apiInstance.projectEnabled();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**ProjectEnabled200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Array of &#x60;ProjectResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectIndex**
> ProjectIndex200Response projectIndex()


### Example

```typescript
import {
    ProjectApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let page: number; // (optional) (default to undefined)
let perPage: number; // (optional) (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `context`, `language`. (optional) (default to undefined)

const { status, data } = await apiInstance.projectIndex(
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
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;context&#x60;, &#x60;language&#x60;. | (optional) defaults to undefined|


### Return type

**ProjectIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | Paginated set of &#x60;ProjectResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectSetEnabled**
> ProjectShow200Response projectSetEnabled(setEnabledProjectRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    SetEnabledProjectRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let setEnabledProjectRequest: SetEnabledProjectRequest; //

const { status, data } = await apiInstance.projectSetEnabled(
    project,
    setEnabledProjectRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **setEnabledProjectRequest** | **SetEnabledProjectRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProjectResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectSetLaunched**
> ProjectShow200Response projectSetLaunched(setLaunchedProjectRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    SetLaunchedProjectRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let setLaunchedProjectRequest: SetLaunchedProjectRequest; //

const { status, data } = await apiInstance.projectSetLaunched(
    project,
    setLaunchedProjectRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **setLaunchedProjectRequest** | **SetLaunchedProjectRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProjectResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectShow**
> ProjectShow200Response projectShow()


### Example

```typescript
import {
    ProjectApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let include: string; //Comma-separated list of related resources to include. Valid values: `context`, `language`. (optional) (default to undefined)

const { status, data } = await apiInstance.projectShow(
    project,
    include
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **project** | [**string**] | The project ID | defaults to undefined|
| **include** | [**string**] | Comma-separated list of related resources to include. Valid values: &#x60;context&#x60;, &#x60;language&#x60;. | (optional) defaults to undefined|


### Return type

**ProjectShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProjectResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectStore**
> ProjectShow200Response projectStore(storeProjectRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    StoreProjectRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let storeProjectRequest: StoreProjectRequest; //

const { status, data } = await apiInstance.projectStore(
    storeProjectRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **storeProjectRequest** | **StoreProjectRequest**|  | |


### Return type

**ProjectShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProjectResource&#x60; |  -  |
|**422** | Validation error |  -  |
|**401** | Unauthenticated |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectUpdate**
> ProjectShow200Response projectUpdate(updateProjectRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    UpdateProjectRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let updateProjectRequest: UpdateProjectRequest; //

const { status, data } = await apiInstance.projectUpdate(
    project,
    updateProjectRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateProjectRequest** | **UpdateProjectRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProjectResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectUpdate2**
> ProjectShow200Response projectUpdate2(updateProjectRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    UpdateProjectRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let updateProjectRequest: UpdateProjectRequest; //

const { status, data } = await apiInstance.projectUpdate2(
    project,
    updateProjectRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **updateProjectRequest** | **UpdateProjectRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;ProjectResource&#x60; |  -  |
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

