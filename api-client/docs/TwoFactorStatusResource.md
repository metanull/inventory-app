# TwoFactorStatusResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**two_factor_enabled** | [**MarkdownPreview422ResponseSuccess**](MarkdownPreview422ResponseSuccess.md) |  | [default to undefined]
**available_methods** | [**TokenAcquire202ResponseAvailableMethods**](TokenAcquire202ResponseAvailableMethods.md) |  | [default to undefined]
**primary_method** | **string** |  | [default to undefined]
**requires_two_factor** | [**MarkdownPreview422ResponseSuccess**](MarkdownPreview422ResponseSuccess.md) |  | [default to undefined]

## Example

```typescript
import { TwoFactorStatusResource } from './api';

const instance: TwoFactorStatusResource = {
    two_factor_enabled,
    available_methods,
    primary_method,
    requires_two_factor,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
