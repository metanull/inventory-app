# TokenAcquireRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**email** | **string** |  | [default to undefined]
**password** | **string** |  | [default to undefined]
**device_name** | **string** |  | [default to undefined]
**wipe_tokens** | **boolean** |  | [optional] [default to undefined]
**two_factor_code** | **string** |  | [optional] [default to undefined]
**recovery_code** | **string** |  | [optional] [default to undefined]

## Example

```typescript
import { TokenAcquireRequest } from './api';

const instance: TokenAcquireRequest = {
    email,
    password,
    device_name,
    wipe_tokens,
    two_factor_code,
    recovery_code,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
