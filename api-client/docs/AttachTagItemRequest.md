# AttachTagItemRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**tag_id** | **string** |  | [default to undefined]
**include** | **string** | Comma-separated list of related resources to include. Valid values: &#x60;partner&#x60;, &#x60;country&#x60;, &#x60;project&#x60;, &#x60;collection&#x60;, &#x60;parent&#x60;, &#x60;children&#x60;, &#x60;itemImages&#x60;, &#x60;artists&#x60;, &#x60;workshops&#x60;, &#x60;tags&#x60;, &#x60;translations&#x60;, &#x60;attachedToCollections&#x60;, &#x60;outgoingLinks&#x60;, &#x60;incomingLinks&#x60;. | [optional] [default to undefined]

## Example

```typescript
import { AttachTagItemRequest } from './api';

const instance: AttachTagItemRequest = {
    tag_id,
    include,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
