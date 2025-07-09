---
layout: default
title: "PictureApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# PictureApi

All URIs are relative to _http://localhost:8000/api_

| Method                                                    | HTTP request                                     | Description                                                                              |
| --------------------------------------------------------- | ------------------------------------------------ | ---------------------------------------------------------------------------------------- |
| [**pictureAttachToDetail**](#pictureattachtodetail)       | **POST** /detail/{detail}/pictures               | Attach an AvailableImage to a Detail                                                     |
| [**pictureAttachToItem**](#pictureattachtoitem)           | **POST** /item/{item}/pictures                   | Attach an AvailableImage to an Item                                                      |
| [**pictureAttachToPartner**](#pictureattachtopartner)     | **POST** /partner/{partner}/pictures             | Attach an AvailableImage to a Partner                                                    |
| [**pictureDestroy**](#picturedestroy)                     | **DELETE** /picture/{picture}                    | Remove the specified resource from storage                                               |
| [**pictureDetachFromDetail**](#picturedetachfromdetail)   | **DELETE** /detail/{detail}/pictures/{picture}   | Detach a Picture from a Detail and convert it back to AvailableImage                     |
| [**pictureDetachFromItem**](#picturedetachfromitem)       | **DELETE** /item/{item}/pictures/{picture}       | Detach a Picture from an Item and convert it back to AvailableImage                      |
| [**pictureDetachFromPartner**](#picturedetachfrompartner) | **DELETE** /partner/{partner}/pictures/{picture} | Detach a Picture from a Partner and convert it back to AvailableImage                    |
| [**pictureDownload**](#picturedownload)                   | **GET** /picture/{picture}/download              | Returns the file to the caller for download                                              |
| [**pictureIndex**](#pictureindex)                         | **GET** /picture                                 | Display a listing of the resource                                                        |
| [**pictureShow**](#pictureshow)                           | **GET** /picture/{picture}                       | Display the specified resource                                                           |
| [**pictureUpdate**](#pictureupdate)                       | **PUT** /picture/{picture}                       | Update the specified resource in storage                                                 |
| [**pictureView**](#pictureview)                           | **GET** /picture/{picture}/view                  | Returns the picture file for direct viewing (e.g., for use in &lt;img&gt; src attribute) |

# **pictureAttachToDetail**

> string pictureAttachToDetail()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let detail: string; //The detail ID (default to undefined)

const { status, data } = await apiInstance.pictureAttachToDetail(detail);
```

### Parameters

| Name       | Type         | Description   | Notes                 |
| ---------- | ------------ | ------------- | --------------------- |
| **detail** | [**string**] | The detail ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureAttachToItem**

> string pictureAttachToItem()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let item: string; //The item ID (default to undefined)

const { status, data } = await apiInstance.pictureAttachToItem(item);
```

### Parameters

| Name     | Type         | Description | Notes                 |
| -------- | ------------ | ----------- | --------------------- |
| **item** | [**string**] | The item ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureAttachToPartner**

> string pictureAttachToPartner()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let partner: string; //The partner ID (default to undefined)

const { status, data } = await apiInstance.pictureAttachToPartner(partner);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **partner** | [**string**] | The partner ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureDestroy**

> pictureDestroy()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureDestroy(picture);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **picture** | [**string**] | The picture ID | defaults to undefined |

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

# **pictureDetachFromDetail**

> string pictureDetachFromDetail()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let detail: string; //The detail ID (default to undefined)
let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureDetachFromDetail(
  detail,
  picture,
);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **detail**  | [**string**] | The detail ID  | defaults to undefined |
| **picture** | [**string**] | The picture ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **422**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureDetachFromItem**

> string pictureDetachFromItem()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let item: string; //The item ID (default to undefined)
let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureDetachFromItem(item, picture);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **item**    | [**string**] | The item ID    | defaults to undefined |
| **picture** | [**string**] | The picture ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **422**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureDetachFromPartner**

> string pictureDetachFromPartner()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let partner: string; //The partner ID (default to undefined)
let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureDetachFromPartner(
  partner,
  picture,
);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **partner** | [**string**] | The partner ID | defaults to undefined |
| **picture** | [**string**] | The picture ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **422**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureDownload**

> string pictureDownload()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureDownload(picture);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **picture** | [**string**] | The picture ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureIndex**

> PictureIndex200Response pictureIndex()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

const { status, data } = await apiInstance.pictureIndex();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**PictureIndex200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                          | Response headers |
| ----------- | ------------------------------------ | ---------------- |
| **200**     | Array of &#x60;PictureResource&#x60; | -                |
| **401**     | Unauthenticated                      | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureShow**

> PictureShow200Response pictureShow()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureShow(picture);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **picture** | [**string**] | The picture ID | defaults to undefined |

### Return type

**PictureShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **200**     | &#x60;PictureResource&#x60; | -                |
| **404**     | Not found                   | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureUpdate**

> PictureShow200Response pictureUpdate(pictureUpdateRequest)

### Example

```typescript
import { PictureApi, Configuration, PictureUpdateRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let picture: string; //The picture ID (default to undefined)
let pictureUpdateRequest: PictureUpdateRequest; //

const { status, data } = await apiInstance.pictureUpdate(
  picture,
  pictureUpdateRequest,
);
```

### Parameters

| Name                     | Type                     | Description    | Notes                 |
| ------------------------ | ------------------------ | -------------- | --------------------- |
| **pictureUpdateRequest** | **PictureUpdateRequest** |                |                       |
| **picture**              | [**string**]             | The picture ID | defaults to undefined |

### Return type

**PictureShow200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description                 | Response headers |
| ----------- | --------------------------- | ---------------- |
| **200**     | &#x60;PictureResource&#x60; | -                |
| **422**     | Validation error            | -                |
| **404**     | Not found                   | -                |
| **401**     | Unauthenticated             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **pictureView**

> string pictureView()

### Example

```typescript
import { PictureApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new PictureApi(configuration);

let picture: string; //The picture ID (default to undefined)

const { status, data } = await apiInstance.pictureView(picture);
```

### Parameters

| Name        | Type         | Description    | Notes                 |
| ----------- | ------------ | -------------- | --------------------- |
| **picture** | [**string**] | The picture ID | defaults to undefined |

### Return type

**string**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description     | Response headers |
| ----------- | --------------- | ---------------- |
| **200**     |                 | -                |
| **404**     | Not found       | -                |
| **401**     | Unauthenticated | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
