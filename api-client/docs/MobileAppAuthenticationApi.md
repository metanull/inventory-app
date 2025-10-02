# MobileAppAuthenticationApi

All URIs are relative to *http://127.0.0.1:8000/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**tokenAcquire**](#tokenacquire) | **POST** /mobile/acquire-token | Acquire a token for the user|
|[**tokenRequestEmailCode**](#tokenrequestemailcode) | **POST** /mobile/request-email-code | Request an email 2FA code for mobile authentication|
|[**tokenTwoFactorStatus**](#tokentwofactorstatus) | **POST** /mobile/two-factor-status | Get user\&#39;s 2FA status and available methods|
|[**tokenVerifyTwoFactor**](#tokenverifytwofactor) | **POST** /mobile/verify-two-factor | Verify two-factor authentication and acquire token|
|[**tokenWipe**](#tokenwipe) | **GET** /mobile/wipe | Revoke all the token for the current user|

# **tokenAcquire**
> TokenAcquire201Response tokenAcquire(tokenAcquireRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    TokenAcquireRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let tokenAcquireRequest: TokenAcquireRequest; //

const { status, data } = await apiInstance.tokenAcquire(
    tokenAcquireRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tokenAcquireRequest** | **TokenAcquireRequest**|  | |


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
|**202** |  |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **tokenRequestEmailCode**
> TokenRequestEmailCode200Response tokenRequestEmailCode(tokenRequestEmailCodeRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    TokenRequestEmailCodeRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let tokenRequestEmailCodeRequest: TokenRequestEmailCodeRequest; //

const { status, data } = await apiInstance.tokenRequestEmailCode(
    tokenRequestEmailCodeRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tokenRequestEmailCodeRequest** | **TokenRequestEmailCodeRequest**|  | |


### Return type

**TokenRequestEmailCode200Response**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **tokenTwoFactorStatus**
> TokenTwoFactorStatus200Response tokenTwoFactorStatus(tokenRequestEmailCodeRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    TokenRequestEmailCodeRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let tokenRequestEmailCodeRequest: TokenRequestEmailCodeRequest; //

const { status, data } = await apiInstance.tokenTwoFactorStatus(
    tokenRequestEmailCodeRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tokenRequestEmailCodeRequest** | **TokenRequestEmailCodeRequest**|  | |


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
|**200** |  |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **tokenVerifyTwoFactor**
> TokenVerifyTwoFactor201Response tokenVerifyTwoFactor(tokenVerifyTwoFactorRequest)


### Example

```typescript
import {
    MobileAppAuthenticationApi,
    Configuration,
    TokenVerifyTwoFactorRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MobileAppAuthenticationApi(configuration);

let tokenVerifyTwoFactorRequest: TokenVerifyTwoFactorRequest; //

const { status, data } = await apiInstance.tokenVerifyTwoFactor(
    tokenVerifyTwoFactorRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **tokenVerifyTwoFactorRequest** | **TokenVerifyTwoFactorRequest**|  | |


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

