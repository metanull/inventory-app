---
layout: default
title: "ContactApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# ContactApi

All URIs are relative to _http://localhost:8000/api_

| Method                                | HTTP request                  | Description                   |
| ------------------------------------- | ----------------------------- | ----------------------------- |
| [**contactDestroy**](#contactdestroy) | **DELETE** /contact/{contact} | Remove the specified contact  |
| [**contactIndex**](#contactindex)     | **GET** /contact              | Display a listing of contacts |
| [**contactShow**](#contactshow)       | **GET** /contact/{contact}    | Display the specified contact |
| [**contactStore**](#contactstore)     | **POST** /contact             | Store a newly created contact |
| [**contactUpdate**](#contactupdate)   | **PUT** /contact/{contact}    | Update the specified contact  |

# **contactDestroy**

> contactDestroy()

### Example

```typescript
import { ContactApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)

const { status, data } = await apiInstance.contactDestroy(contact);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **contact** | [**string**] | The contact ID | defaults to undefined |

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

# **contactIndex**

> ContactIndex200Response contactIndex()

### Example

```typescript
import { ContactApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

const { status, data } = await apiInstance.contactIndex();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**ContactIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                          | Response headers |
| ----------- | ------------------------------------ | ---------------- |
| **200**     | Array of &#x60;ContactResource&#x60; | -                |
| **401**     | Unauthenticated                      | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contactShow**

> ContactStore201Response contactShow()

### Example

```typescript
import { ContactApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)

const { status, data } = await apiInstance.contactShow(contact);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **contact** | [**string**] | The contact ID | defaults to undefined |

### Return type

**ContactStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **200**     | &#x60;ContactResource&#x60; | -                |
| **404**     | Not found                   | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contactStore**

> ContactStore201Response contactStore(contactStoreRequest)

### Example

```typescript
import { ContactApi, Configuration, ContactStoreRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contactStoreRequest: ContactStoreRequest; //

const { status, data } = await apiInstance.contactStore(contactStoreRequest);
```

### Parameters

| Name                    | Type                    | Description | Notes |
| ----------------------- | ----------------------- | ----------- | ----- |
| **contactStoreRequest** | **ContactStoreRequest** |             |       |

### Return type

**ContactStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **201**     | &#x60;ContactResource&#x60; | -                |
| **422**     |                             | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **contactUpdate**

> ContactStore201Response contactUpdate(contactUpdateRequest)

### Example

```typescript
import { ContactApi, Configuration, ContactUpdateRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new ContactApi(configuration);

let contact: string; //The contact ID (default to undefined)
let contactUpdateRequest: ContactUpdateRequest; //

const { status, data } = await apiInstance.contactUpdate(
  contact,
  contactUpdateRequest,
);
```

### Parameters

| Name                     | Type                     | Description    | Notes                 |
| ------------------------ | ------------------------ | -------------- | --------------------- |
| **contactUpdateRequest** | **ContactUpdateRequest** |                |                       |
| **contact**              | [**string**]             | The contact ID | defaults to undefined |

### Return type

**ContactStore201Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **200**     | &#x60;ContactResource&#x60; | -                |
| **422**     |                             | -                |
| **404**     | Not found                   | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
