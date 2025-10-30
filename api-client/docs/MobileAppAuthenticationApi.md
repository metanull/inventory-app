# MobileAppAuthenticationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**tokenAcquire**](#tokenacquire) | **POST** /mobile/acquire-token | Acquire a token for the user|
|[**tokenTwoFactorStatus**](#tokentwofactorstatus) | **POST** /mobile/two-factor-status | Get user\&#39;s 2FA status|
|[**tokenVerifyTwoFactor**](#tokenverifytwofactor) | **POST** /mobile/verify-two-factor | Verify two-factor authentication and acquire token|
|[**tokenWipe**](#tokenwipe) | **GET** /mobile/wipe | Revoke all the token for the current user|

# **tokenAcquire**
> TokenAcquire201Response tokenAcquire(acquireTokenMobileAppAuthenticationRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    AcquireTokenMobileAppAuthenticationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let acquireTokenMobileAppAuthenticationRequest: AcquireTokenMobileAppAuthenticationRequest; //

const { status, data } = await apiInstance.tokenAcquire(
    acquireTokenMobileAppAuthenticationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **acquireTokenMobileAppAuthenticationRequest** | **AcquireTokenMobileAppAuthenticationRequest**|  | |


### Return type

**TokenAcquire201Response**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** |  |  -  |
|**202** | No 2FA code provided, require 2FA |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **tokenTwoFactorStatus**
> TokenTwoFactorStatus200Response tokenTwoFactorStatus(twoFactorStatusMobileAppAuthenticationRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    TwoFactorStatusMobileAppAuthenticationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let twoFactorStatusMobileAppAuthenticationRequest: TwoFactorStatusMobileAppAuthenticationRequest; //

const { status, data } = await apiInstance.tokenTwoFactorStatus(
    twoFactorStatusMobileAppAuthenticationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **twoFactorStatusMobileAppAuthenticationRequest** | **TwoFactorStatusMobileAppAuthenticationRequest**|  | |


### Return type

**TokenTwoFactorStatus200Response**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** | &#x60;TwoFactorStatusResource&#x60; |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **tokenVerifyTwoFactor**
> TokenVerifyTwoFactor201Response tokenVerifyTwoFactor(verifyTwoFactorMobileAppAuthenticationRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    VerifyTwoFactorMobileAppAuthenticationRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let verifyTwoFactorMobileAppAuthenticationRequest: VerifyTwoFactorMobileAppAuthenticationRequest; //

const { status, data } = await apiInstance.tokenVerifyTwoFactor(
    verifyTwoFactorMobileAppAuthenticationRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **verifyTwoFactorMobileAppAuthenticationRequest** | **VerifyTwoFactorMobileAppAuthenticationRequest**|  | |


### Return type

**TokenVerifyTwoFactor201Response**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**201** |  |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **tokenWipe**
> tokenWipe()


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

const { status, data } = await apiInstance.tokenWipe();
```

### Parameters
This endpoint does not have any parameters.


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
|**401** | Unauthenticated |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

