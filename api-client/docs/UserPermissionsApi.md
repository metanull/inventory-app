# UserPermissionsApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**userPermissions**](#userpermissions) | **GET** /user/permissions | Get the authenticated user\&#39;s permissions|

# **userPermissions**
> UserPermissions200Response userPermissions()

Returns a list of permission names that the authenticated user has. This is a read-only endpoint for UI clients to determine what features to show to the user.

### Example

```typescript
import {
    UserPermissionsApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new UserPermissionsApi(configuration);

const { status, data } = await apiInstance.userPermissions();
```

### Parameters
This endpoint does not have any parameters.


### Return type

**UserPermissions200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;UserPermissionsResource&#x60; |  -  |
|**401** | Unauthenticated |  -  |
|**422** | Validation error |  -  |
|**403** | Authorization error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

