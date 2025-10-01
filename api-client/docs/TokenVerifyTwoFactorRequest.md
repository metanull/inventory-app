# TokenVerifyTwoFactorRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**email** | **string** |  | [default to undefined]
**password** | **string** |  | [default to undefined]
**device_name** | **string** |  | [default to undefined]
**code** | **string** |  | [default to undefined]
**method** | **string** |  | [optional] [default to undefined]
**wipe_tokens** | **boolean** |  | [optional] [default to undefined]

## Example

```typescript
import { TokenVerifyTwoFactorRequest } from './api';

const instance: TokenVerifyTwoFactorRequest = {
    email,
    password,
    device_name,
    code,
    method,
    wipe_tokens,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
