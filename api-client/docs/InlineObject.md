# InlineObject


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**message** | **string** | Errors overview. | [default to undefined]
**errors** | **{ [key: string]: Array&lt;string&gt;; }** | A detailed description of each field that failed validation. | [default to undefined]

## Example

```typescript
import { InlineObject } from './api';

const instance: InlineObject = {
    message,
    errors,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
