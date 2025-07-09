# DetailResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier of the item (GUID) | [default to undefined]
**internal_name** | **string** | The name of the item, it shall only be used internally | [default to undefined]
**item** | [**ItemResource**](ItemResource.md) | The item this detail belongs to | [optional] [default to undefined]
**backward_compatibility** | **string** | The legacy Id when this item corresponds to a legacy item from the MWNF3 database, nullable | [default to undefined]
**translations** | [**Array&lt;DetailTranslationResource&gt;**](DetailTranslationResource.md) | Translations for this detail (internationalization and contextualization) | [optional] [default to undefined]
**created_at** | **string** | Date of creation | [default to undefined]
**updated_at** | **string** | Date of last modification | [default to undefined]

## Example

```typescript
import { DetailResource } from './api';

const instance: DetailResource = {
    id,
    internal_name,
    item,
    backward_compatibility,
    translations,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
