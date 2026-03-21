# PartnerResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique identifier (GUID) | [default to undefined]
**internal_name** | **string** | A name for this resource, for internal use only. | [default to undefined]
**backward_compatibility** | **string** | The Id(s) of matching resource in the legacy system (if any). | [default to undefined]
**type** | **string** | The type of the partner, either \&#39;museum\&#39;, \&#39;institution\&#39; or \&#39;individual\&#39;. | [default to undefined]
**country** | [**CountryResource**](CountryResource.md) | The country this partner is associated with, nullable (CountryResource) | [optional] [default to undefined]
**latitude** | **string** | GPS Location | [default to undefined]
**longitude** | **string** |  | [default to undefined]
**map_zoom** | **string** |  | [default to undefined]
**project** | [**ProjectResource**](ProjectResource.md) | Relationships | [optional] [default to undefined]
**monument_item** | [**ItemResource**](ItemResource.md) |  | [optional] [default to undefined]
**translations** | [**Array&lt;PartnerTranslationResource&gt;**](PartnerTranslationResource.md) |  | [optional] [default to undefined]
**images** | [**Array&lt;PartnerImageResource&gt;**](PartnerImageResource.md) |  | [optional] [default to undefined]
**collections** | [**Array&lt;CollectionResource&gt;**](CollectionResource.md) |  | [optional] [default to undefined]
**visible** | **string** | Visibility | [default to undefined]
**created_at** | **string** | The date of creation of the resource (managed by the system) | [default to undefined]
**updated_at** | **string** | The date of last modification of the resource (managed by the system) | [default to undefined]

## Example

```typescript
import { PartnerResource } from './api';

const instance: PartnerResource = {
    id,
    internal_name,
    backward_compatibility,
    type,
    country,
    latitude,
    longitude,
    map_zoom,
    project,
    monument_item,
    translations,
    images,
    collections,
    visible,
    created_at,
    updated_at,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
