---
layout: default
title: "DetailTranslationStoreRequest"
parent: TypeScript API Client
nav_order: 1
category: "Requests"
---

# DetailTranslationStoreRequest


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**detail_id** | **string** |  | [default to undefined]
**language_id** | **string** |  | [default to undefined]
**context_id** | **string** |  | [default to undefined]
**name** | **string** |  | [default to undefined]
**alternate_name** | **string** |  | [optional] [default to undefined]
**description** | **string** |  | [default to undefined]
**author_id** | **string** |  | [optional] [default to undefined]
**text_copy_editor_id** | **string** |  | [optional] [default to undefined]
**translator_id** | **string** |  | [optional] [default to undefined]
**translation_copy_editor_id** | **string** |  | [optional] [default to undefined]
**backward_compatibility** | **string** |  | [optional] [default to undefined]
**extra** | **Array&lt;string&gt;** |  | [optional] [default to undefined]

## Example

```typescript
import { DetailTranslationStoreRequest } from './api';

const instance: DetailTranslationStoreRequest = {
    detail_id,
    language_id,
    context_id,
    name,
    alternate_name,
    description,
    author_id,
    text_copy_editor_id,
    translator_id,
    translation_copy_editor_id,
    backward_compatibility,
    extra,
};
```

[Back to Model list]({{ site.baseurl }}/api-client/) [Back to API list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)


---

*This documentation was automatically generated from the TypeScript API client.*
