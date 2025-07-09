---
layout: default
title: "ProvinceTranslationApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# ProvinceTranslationApi

All URIs are relative to _http://localhost:8000/api_

| Method                                                        | HTTP request                                           | Description                                |
| ------------------------------------------------------------- | ------------------------------------------------------ | ------------------------------------------ |
| [**provinceTranslationDestroy**](#provincetranslationdestroy) | **DELETE** /province-translation/{provinceTranslation} | Remove the specified resource from storage |
| [**provinceTranslationIndex**](#provincetranslationindex)     | **GET** /province-translation                          | Display a listing of the resource          |
| [**provinceTranslationShow**](#provincetranslationshow)       | **GET** /province-translation/{provinceTranslation}    | Display the specified resource             |
| [**provinceTranslationStore**](#provincetranslationstore)     | **POST** /province-translation                         | Store a newly created resource in storage  |
| [**provinceTranslationUpdate**](#provincetranslationupdate)   | **PUT** /province-translation/{provinceTranslation}    | Update the specified resource in storage   |

# **provinceTranslationDestroy**

> provinceTranslationDestroy()

### Example

```typescript
import { ProvinceTranslationApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)

const { status, data } =
  await apiInstance.provinceTranslationDestroy(provinceTranslation);
```

### Parameters

| Name                    | Type         | Description                 | Notes                 |
| ----------------------- | ------------ | --------------------------- | --------------------- |
| **provinceTranslation** | [**string**] | The province translation ID | defaults to undefined |

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

# **provinceTranslationIndex**

> ProvinceTranslationIndex200Response provinceTranslationIndex()

### Example

```typescript
import { ProvinceTranslationApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

const { status, data } = await apiInstance.provinceTranslationIndex();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**ProvinceTranslationIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                                      | Response headers |
| ----------- | ------------------------------------------------ | ---------------- |
| **200**     | Array of &#x60;ProvinceTranslationResource&#x60; | -                |
| **401**     | Unauthenticated                                  | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceTranslationShow**

> ProvinceTranslationStore200Response provinceTranslationShow()

### Example

```typescript
import { ProvinceTranslationApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)

const { status, data } =
  await apiInstance.provinceTranslationShow(provinceTranslation);
```

### Parameters

| Name                    | Type         | Description                 | Notes                 |
| ----------------------- | ------------ | --------------------------- | --------------------- |
| **provinceTranslation** | [**string**] | The province translation ID | defaults to undefined |

### Return type

**ProvinceTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                             | Response headers |
| ----------- | --------------------------------------- | ---------------- |
| **200**     | &#x60;ProvinceTranslationResource&#x60; | -                |
| **404**     | Not found                               | -                |
| **401**     | Unauthenticated                         | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceTranslationStore**

> ProvinceTranslationStore200Response provinceTranslationStore(provinceTranslationStoreRequest)

### Example

```typescript
import {
  ProvinceTranslationApi,
  Configuration,
  ProvinceTranslationStoreRequest,
} from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslationStoreRequest: ProvinceTranslationStoreRequest; //

const { status, data } = await apiInstance.provinceTranslationStore(
  provinceTranslationStoreRequest,
);
```

### Parameters

| Name                                | Type                                | Description | Notes |
| ----------------------------------- | ----------------------------------- | ----------- | ----- |
| **provinceTranslationStoreRequest** | **ProvinceTranslationStoreRequest** |             |       |

### Return type

**ProvinceTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                             | Response headers |
| ----------- | --------------------------------------- | ---------------- |
| **200**     | &#x60;ProvinceTranslationResource&#x60; | -                |
| **422**     | Validation error                        | -                |
| **401**     | Unauthenticated                         | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **provinceTranslationUpdate**

> ProvinceTranslationStore200Response provinceTranslationUpdate()

### Example

```typescript
import {
  ProvinceTranslationApi,
  Configuration,
  ProvinceTranslationUpdateRequest,
} from "./api";

const configuration = new Configuration();
const apiInstance = new ProvinceTranslationApi(configuration);

let provinceTranslation: string; //The province translation ID (default to undefined)
let provinceTranslationUpdateRequest: ProvinceTranslationUpdateRequest; // (optional)

const { status, data } = await apiInstance.provinceTranslationUpdate(
  provinceTranslation,
  provinceTranslationUpdateRequest,
);
```

### Parameters

| Name                                 | Type                                 | Description                 | Notes                 |
| ------------------------------------ | ------------------------------------ | --------------------------- | --------------------- |
| **provinceTranslationUpdateRequest** | **ProvinceTranslationUpdateRequest** |                             |                       |
| **provinceTranslation**              | [**string**]                         | The province translation ID | defaults to undefined |

### Return type

**ProvinceTranslationStore200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                             | Response headers |
| ----------- | --------------------------------------- | ---------------- |
| **200**     | &#x60;ProvinceTranslationResource&#x60; | -                |
| **422**     | Validation error                        | -                |
| **404**     | Not found                               | -                |
| **401**     | Unauthenticated                         | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
