# MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi

All URIs are relative to *http://localhost/api*

|Method | HTTP request | Description|
|------------- | ------------- | -------------|
|[**markdownAllowedElements**](#markdownallowedelements) | **GET** /markdown/allowed-elements | Get allowed HTML tags|
|[**markdownFromHtml**](#markdownfromhtml) | **POST** /markdown/from-html | Convert HTML to Markdown|
|[**markdownIsMarkdown**](#markdownismarkdown) | **POST** /markdown/is-markdown | Check if content is Markdown|
|[**markdownPreview**](#markdownpreview) | **POST** /markdown/preview | Preview Markdown content|
|[**markdownToHtml**](#markdowntohtml) | **POST** /markdown/to-html | Convert Markdown to HTML|
|[**markdownValidate**](#markdownvalidate) | **POST** /markdown/validate | Validate Markdown content|

# **markdownAllowedElements**
> number markdownAllowedElements()

Returns the list of HTML tags that are supported for HTML-to-Markdown conversion.

### Example

```typescript
import {
    MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi(configuration);

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
|-------------|-------------|------------------|
|**200** |  |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **markdownFromHtml**
> number markdownFromHtml(markdownFromHtmlRequest)

Converts HTML content to Markdown format with controlled tag support. Only allowed HTML tags will be processed, others will be stripped.

### Example

```typescript
import {
    MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi,
    Configuration,
    MarkdownFromHtmlRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi(configuration);

let markdownFromHtmlRequest: MarkdownFromHtmlRequest; //

const { status, data } = await apiInstance.markdownFromHtml(
    markdownFromHtmlRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **markdownFromHtmlRequest** | **MarkdownFromHtmlRequest**|  | |


### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **markdownIsMarkdown**
> number markdownIsMarkdown(markdownIsMarkdownRequest)

Analyzes content to determine if it appears to contain Markdown formatting. Useful for automatic detection of content type.

### Example

```typescript
import {
    MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi,
    Configuration,
    MarkdownIsMarkdownRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi(configuration);

let markdownIsMarkdownRequest: MarkdownIsMarkdownRequest; //

const { status, data } = await apiInstance.markdownIsMarkdown(
    markdownIsMarkdownRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **markdownIsMarkdownRequest** | **MarkdownIsMarkdownRequest**|  | |


### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **markdownPreview**
> MarkdownPreview200Response markdownPreview()

Generates an HTML preview of Markdown content for display purposes. This is essentially the same as markdownToHtml but with a different semantic meaning.

### Example

```typescript
import {
    MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi,
    Configuration
} from './api';

const configuration = new Configuration();
const apiInstance = new MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi(configuration);

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
|-------------|-------------|------------------|
|**500** |  |  -  |
|**200** |  |  -  |
|**422** |  |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **markdownToHtml**
> number markdownToHtml(markdownToHtmlRequest)

Converts Markdown formatted text to HTML for display purposes. The input is validated to ensure it contains safe Markdown content.

### Example

```typescript
import {
    MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi,
    Configuration,
    MarkdownToHtmlRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi(configuration);

let markdownToHtmlRequest: MarkdownToHtmlRequest; //

const { status, data } = await apiInstance.markdownToHtml(
    markdownToHtmlRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **markdownToHtmlRequest** | **MarkdownToHtmlRequest**|  | |


### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |
|**422** | Validation error |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

# **markdownValidate**
> number markdownValidate(markdownToHtmlRequest)

Validates Markdown content without converting it, useful for form validation.

### Example

```typescript
import {
    MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi,
    Configuration,
    MarkdownToHtmlRequest
} from './api';

const configuration = new Configuration();
const apiInstance = new MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi(configuration);

let markdownToHtmlRequest: MarkdownToHtmlRequest; //

const { status, data } = await apiInstance.markdownValidate(
    markdownToHtmlRequest
);
```

### Parameters

|Name | Type | Description  | Notes|
|------------- | ------------- | ------------- | -------------|
| **markdownToHtmlRequest** | **MarkdownToHtmlRequest**|  | |


### Return type

**number**

### Authorization

[http](../README.md#http)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json


### HTTP response details
| Status code | Description | Response headers |
|-------------|-------------|------------------|
|**200** |  |  -  |

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to Model list]](../README.md#documentation-for-models) [[Back to README]](../README.md)

