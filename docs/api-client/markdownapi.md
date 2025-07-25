---
layout: default
title: "MarkdownApi"
parent: TypeScript API Client
nav_order: 1
category: "APIs"
---

# MarkdownApi

All URIs are relative to _http://localhost:8000/api_

| Method                                                  | HTTP request                       | Description                  |
| ------------------------------------------------------- | ---------------------------------- | ---------------------------- |
| [**markdownAllowedElements**](#markdownallowedelements) | **GET** /markdown/allowed-elements | Get allowed HTML tags        |
| [**markdownFromHtml**](#markdownfromhtml)               | **POST** /markdown/from-html       | Convert HTML to Markdown     |
| [**markdownIsMarkdown**](#markdownismarkdown)           | **POST** /markdown/is-markdown     | Check if content is Markdown |
| [**markdownPreview**](#markdownpreview)                 | **POST** /markdown/preview         | Preview Markdown content     |
| [**markdownToHtml**](#markdowntohtml)                   | **POST** /markdown/to-html         | Convert Markdown to HTML     |
| [**markdownValidate**](#markdownvalidate)               | **POST** /markdown/validate        | Validate Markdown content    |

# **markdownAllowedElements**

> number markdownAllowedElements()

Returns the list of HTML tags that are supported for HTML-to-Markdown conversion.

### Example

```typescript
import { MarkdownApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new MarkdownApi(configuration);

const { status, data } = await apiInstance.markdownAllowedElements();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
| ----------- | ----------- | ---------------- |
| **200**     |             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **markdownFromHtml**

> number markdownFromHtml(markdownFromHtmlRequest)

Converts HTML content to Markdown format with controlled tag support. Only allowed HTML tags will be processed, others will be stripped.

### Example

```typescript
import { MarkdownApi, Configuration, MarkdownFromHtmlRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new MarkdownApi(configuration);

let markdownFromHtmlRequest: MarkdownFromHtmlRequest; //

const { status, data } = await apiInstance.markdownFromHtml(
  markdownFromHtmlRequest,
);
```

### Parameters

| Name                        | Type                        | Description | Notes |
| --------------------------- | --------------------------- | ----------- | ----- |
| **markdownFromHtmlRequest** | **MarkdownFromHtmlRequest** |             |       |

### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description      | Response headers |
| ----------- | ---------------- | ---------------- |
| **200**     |                  | -                |
| **422**     | Validation error | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **markdownIsMarkdown**

> number markdownIsMarkdown(markdownIsMarkdownRequest)

Analyzes content to determine if it appears to contain Markdown formatting. Useful for automatic detection of content type.

### Example

```typescript
import { MarkdownApi, Configuration, MarkdownIsMarkdownRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new MarkdownApi(configuration);

let markdownIsMarkdownRequest: MarkdownIsMarkdownRequest; //

const { status, data } = await apiInstance.markdownIsMarkdown(
  markdownIsMarkdownRequest,
);
```

### Parameters

| Name                          | Type                          | Description | Notes |
| ----------------------------- | ----------------------------- | ----------- | ----- |
| **markdownIsMarkdownRequest** | **MarkdownIsMarkdownRequest** |             |       |

### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
| ----------- | ----------- | ---------------- |
| **200**     |             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **markdownPreview**

> MarkdownPreview200Response markdownPreview()

Generates an HTML preview of Markdown content for display purposes. This is essentially the same as markdownToHtml but with a different semantic meaning.

### Example

```typescript
import { MarkdownApi, Configuration } from "./api";

const configuration = new Configuration();
const apiInstance = new MarkdownApi(configuration);

const { status, data } = await apiInstance.markdownPreview();
```

### Parameters

This endpoint does not have any parameters.

### Return type

**MarkdownPreview200Response**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
| ----------- | ----------- | ---------------- |
| **500**     |             | -                |
| **200**     |             | -                |
| **422**     |             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **markdownToHtml**

> number markdownToHtml(markdownToHtmlRequest)

Converts Markdown formatted text to HTML for display purposes. The input is validated to ensure it contains safe Markdown content.

### Example

```typescript
import { MarkdownApi, Configuration, MarkdownToHtmlRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new MarkdownApi(configuration);

let markdownToHtmlRequest: MarkdownToHtmlRequest; //

const { status, data } = await apiInstance.markdownToHtml(
  markdownToHtmlRequest,
);
```

### Parameters

| Name                      | Type                      | Description | Notes |
| ------------------------- | ------------------------- | ----------- | ----- |
| **markdownToHtmlRequest** | **MarkdownToHtmlRequest** |             |       |

### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description      | Response headers |
| ----------- | ---------------- | ---------------- |
| **200**     |                  | -                |
| **422**     | Validation error | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

# **markdownValidate**

> number markdownValidate(markdownToHtmlRequest)

Validates Markdown content without converting it, useful for form validation.

### Example

```typescript
import { MarkdownApi, Configuration, MarkdownToHtmlRequest } from "./api";

const configuration = new Configuration();
const apiInstance = new MarkdownApi(configuration);

let markdownToHtmlRequest: MarkdownToHtmlRequest; //

const { status, data } = await apiInstance.markdownValidate(
  markdownToHtmlRequest,
);
```

### Parameters

| Name                      | Type                      | Description | Notes |
| ------------------------- | ------------------------- | ----------- | ----- |
| **markdownToHtmlRequest** | **MarkdownToHtmlRequest** |             |       |

### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json

### HTTP response details

| Status code | Description | Response headers |
| ----------- | ----------- | ---------------- |
| **200**     |             | -                |

[Back to top](#) [Back to API list]({{ site.baseurl }}/api-client/) [Back to Model list]({{ site.baseurl }}/api-client/) [Back to README]({{ site.baseurl }}/api-client/)

---

_This documentation was automatically generated from the TypeScript API client._
