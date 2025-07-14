# ProjectApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**projectDestroy**](#projectdestroy) | **DELETE** /project/{project} | Remove the specified resource from storage|
|[**projectEnabled**](#projectenabled) | **GET** /project/enabled | Get all enabled projects (Enabled + launched)|
|[**projectIndex**](#projectindex) | **GET** /project | Display a listing of the resource|
|[**projectSetEnabled**](#projectsetenabled) | **PATCH** /project/{project}/enabled | Toggle Enable/disable on a project|
|[**projectSetLaunched**](#projectsetlaunched) | **PATCH** /project/{project}/launched | Toggle Launched/not-launched on a project|
|[**projectShow**](#projectshow) | **GET** /project/{project} | Display the specified resource|
|[**projectStore**](#projectstore) | **POST** /project | Store a newly created resource in storage|
|[**projectUpdate**](#projectupdate) | **PUT** /project/{project} | Update the specified resource in storage|

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
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectIndex**
> ProjectEnabled200Response projectIndex()


### Example

```typescript
import {
    ProjectApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

const { status, data } = await apiInstance.projectIndex();
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
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectSetEnabled**
> ProjectSetLaunched200Response projectSetEnabled(projectSetEnabledRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    ProjectSetEnabledRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let projectSetEnabledRequest: ProjectSetEnabledRequest; //

const { status, data } = await apiInstance.projectSetEnabled(
    project,
    projectSetEnabledRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **projectSetEnabledRequest** | **ProjectSetEnabledRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectSetLaunched200Response**

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
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectSetLaunched**
> ProjectSetLaunched200Response projectSetLaunched()


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    ProjectSetLaunchedRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let projectSetLaunchedRequest: ProjectSetLaunchedRequest; // (optional)

const { status, data } = await apiInstance.projectSetLaunched(
    project,
    projectSetLaunchedRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **projectSetLaunchedRequest** | **ProjectSetLaunchedRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectSetLaunched200Response**

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
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectShow**
> ProjectSetLaunched200Response projectShow()


### Example

```typescript
import {
    ProjectApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)

const { status, data } = await apiInstance.projectShow(
    project
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectSetLaunched200Response**

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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectStore**
> ProjectSetLaunched200Response projectStore(projectStoreRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    ProjectStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let projectStoreRequest: ProjectStoreRequest; //

const { status, data } = await apiInstance.projectStore(
    projectStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **projectStoreRequest** | **ProjectStoreRequest**|  | |


### Return type

**ProjectSetLaunched200Response**

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

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **projectUpdate**
> ProjectSetLaunched200Response projectUpdate(projectStoreRequest)


### Example

```typescript
import {
    ProjectApi,
    Configuration,
    ProjectStoreRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new ProjectApi(configuration);

let project: string; //The project ID (default to undefined)
let projectStoreRequest: ProjectStoreRequest; //

const { status, data } = await apiInstance.projectUpdate(
    project,
    projectStoreRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **projectStoreRequest** | **ProjectStoreRequest**|  | |
| **project** | [**string**] | The project ID | defaults to undefined|


### Return type

**ProjectSetLaunched200Response**

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
|**404** | Not found |  -  |
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

