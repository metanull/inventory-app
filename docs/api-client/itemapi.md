---
layout: default
title: "ItemApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# ItemApi

All URIs are relative to _http://localhost:8000/api_

| Method                                  | HTTP request                 | Description                                                                |
| --------------------------------------- | ---------------------------- | -------------------------------------------------------------------------- |
| [**itemDestroy**](#itemdestroy)         | **DELETE** /item/{item}      | Remove the specified resource from storage                                 |
| [**itemForTag**](#itemfortag)           | **GET** /item/for-tag/{tag}  | Get items for a specific tag                                               |
| [**itemIndex**](#itemindex)             | **GET** /item                | Display a listing of the resource                                          |
| [**itemShow**](#itemshow)               | **GET** /item/{item}         | Display the specified resource                                             |
| [**itemStore**](#itemstore)             | **POST** /item               | Store a newly created resource in storage                                  |
| [**itemUpdate**](#itemupdate)           | **PUT** /item/{item}         | Update the specified resource in storage                                   |
| [**itemUpdateTags**](#itemupdatetags)   | **PATCH** /item/{item}/tags  | Update tags for the specified item without modifying other item properties |
| [**itemWithAllTags**](#itemwithalltags) | **POST** /item/with-all-tags | Get items that have ALL of the specified tags (AND condition)              |
| [**itemWithAnyTags**](#itemwithanytags) | **POST** /item/with-any-tags | Get items that have ANY of the specified tags (OR condition)               |

# **itemDestroy**

> itemDestroy()

### Example

```typescript
import { ItemApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)

const { status, data } = await apiInstance.itemDestroy(item);
```

### Parameters

| Name     | Type         | Description | Notes                 |
| -------- | ------------ | ----------- | --------------------- |
| **item** | [**string**] | The item ID | defaults to undefined |

### Return type

void (empty response body)

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **204**     | No content      | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemForTag**

> ItemIndex200Response itemForTag()

### Example

```typescript
import { ItemApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let tag: string; //The tag ID (default to undefined)

const { status, data } = await apiInstance.itemForTag(tag);
```

### Parameters

| Name    | Type         | Description | Notes                 |
| ------- | ------------ | ----------- | --------------------- |
| **tag** | [**string**] | The tag ID  | defaults to undefined |

### Return type

**ItemIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                       | Response headers |
| ----------- | --------------------------------- | ---------------- |
| **200**     | Array of &#x60;ItemResource&#x60; | -                |
| **404**     | Not found                         | -                |
| **401**     | Unauthenticated                   | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemIndex**

> ItemIndex200Response itemIndex()

### Example

```typescript
import { ItemApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

const { status, data } = await apiInstance.itemIndex();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**ItemIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                       | Response headers |
| ----------- | --------------------------------- | ---------------- |
| **200**     | Array of &#x60;ItemResource&#x60; | -                |
| **401**     | Unauthenticated                   | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemShow**

> ItemStore200Response itemShow()

### Example

```typescript
import { ItemApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)

const { status, data } = await apiInstance.itemShow(item);
```

### Parameters

| Name     | Type         | Description | Notes                 |
| -------- | ------------ | ----------- | --------------------- |
| **item** | [**string**] | The item ID | defaults to undefined |

### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description              | Response headers |
| ----------- | ------------------------ | ---------------- |
| **200**     | &#x60;ItemResource&#x60; | -                |
| **404**     | Not found                | -                |
| **401**     | Unauthenticated          | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemStore**

> ItemStore200Response itemStore(itemStoreRequest)

### Example

```typescript
import { ItemApi, Configuration, ItemStoreRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let itemStoreRequest: ItemStoreRequest; //

const { status, data } = await apiInstance.itemStore(itemStoreRequest);
```

### Parameters

| Name                 | Type                 | Description | Notes |
| -------------------- | -------------------- | ----------- | ----- |
| **itemStoreRequest** | **ItemStoreRequest** |             |       |

### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description              | Response headers |
| ----------- | ------------------------ | ---------------- |
| **200**     | &#x60;ItemResource&#x60; | -                |
| **422**     | Validation error         | -                |
| **401**     | Unauthenticated          | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemUpdate**

> ItemStore200Response itemUpdate(itemStoreRequest)

### Example

```typescript
import { ItemApi, Configuration, ItemStoreRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //The item ID (default to undefined)
let itemStoreRequest: ItemStoreRequest; //

const { status, data } = await apiInstance.itemUpdate(item, itemStoreRequest);
```

### Parameters

| Name                 | Type                 | Description | Notes                 |
| -------------------- | -------------------- | ----------- | --------------------- |
| **itemStoreRequest** | **ItemStoreRequest** |             |                       |
| **item**             | [**string**]         | The item ID | defaults to undefined |

### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description              | Response headers |
| ----------- | ------------------------ | ---------------- |
| **200**     | &#x60;ItemResource&#x60; | -                |
| **422**     | Validation error         | -                |
| **404**     | Not found                | -                |
| **401**     | Unauthenticated          | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemUpdateTags**

> ItemStore200Response itemUpdateTags()

This endpoint allows quick editing of tag associations by specifying which tags to attach or detach from the item. It provides fine-grained control over tag operations without requiring a full item update.

### Example

```typescript
import { ItemApi, Configuration, ItemUpdateTagsRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let item: string; //- The item to update tags for (default to undefined)
let itemUpdateTagsRequest: ItemUpdateTagsRequest; // (optional)

const { status, data } = await apiInstance.itemUpdateTags(
  item,
  itemUpdateTagsRequest,
);
```

### Parameters

| Name                      | Type                      | Description                   | Notes                 |
| ------------------------- | ------------------------- | ----------------------------- | --------------------- |
| **itemUpdateTagsRequest** | **ItemUpdateTagsRequest** |                               |                       |
| **item**                  | [**string**]              | - The item to update tags for | defaults to undefined |

### Return type

**ItemStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description              | Response headers |
| ----------- | ------------------------ | ---------------- |
| **200**     | &#x60;ItemResource&#x60; | -                |
| **422**     | Validation error         | -                |
| **404**     | Not found                | -                |
| **401**     | Unauthenticated          | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemWithAllTags**

> ItemIndex200Response itemWithAllTags(itemWithAllTagsRequest)

### Example

```typescript
import { ItemApi, Configuration, ItemWithAllTagsRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let itemWithAllTagsRequest: ItemWithAllTagsRequest; //

const { status, data } = await apiInstance.itemWithAllTags(
  itemWithAllTagsRequest,
);
```

### Parameters

| Name                       | Type                       | Description | Notes |
| -------------------------- | -------------------------- | ----------- | ----- |
| **itemWithAllTagsRequest** | **ItemWithAllTagsRequest** |             |       |

### Return type

**ItemIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                       | Response headers |
| ----------- | --------------------------------- | ---------------- |
| **200**     | Array of &#x60;ItemResource&#x60; | -                |
| **422**     | Validation error                  | -                |
| **401**     | Unauthenticated                   | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **itemWithAnyTags**

> ItemIndex200Response itemWithAnyTags(itemWithAllTagsRequest)

### Example

```typescript
import { ItemApi, Configuration, ItemWithAllTagsRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ItemApi(configuration);

let itemWithAllTagsRequest: ItemWithAllTagsRequest; //

const { status, data } = await apiInstance.itemWithAnyTags(
  itemWithAllTagsRequest,
);
```

### Parameters

| Name                       | Type                       | Description | Notes |
| -------------------------- | -------------------------- | ----------- | ----- |
| **itemWithAllTagsRequest** | **ItemWithAllTagsRequest** |             |       |

### Return type

**ItemIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                       | Response headers |
| ----------- | --------------------------------- | ---------------- |
| **200**     | Array of &#x60;ItemResource&#x60; | -                |
| **422**     | Validation error                  | -                |
| **401**     | Unauthenticated                   | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
