<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\MarkdownRule;
use App\Services\MarkdownService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @tags Markdown
 *
 * API endpoints for Markdown processing and conversion
 */
class MarkdownController extends Controller
{
    private MarkdownService $markdownService;

    public function __construct(MarkdownService $markdownService)
    {
        $this->markdownService = $markdownService;
    }

    /**
     * Convert Markdown to HTML
     *
     * Converts Markdown formatted text to HTML for display purposes.
     * The input is validated to ensure it contains safe Markdown content.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "html": "<p>Converted HTML</p>"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "markdown": ["The markdown contains potentially unsafe content."]
     *   }
     * }
     */
    public function markdownToHtml(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'markdown' => ['required', 'string', new MarkdownRule],
        ]);

        if ($validator->fails()) {
            return response()->json(
                (new \App\Http\Resources\ConversionResource([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]))->toArray(request()),
                422
            );
        }

        try {
            $html = $this->markdownService->markdownToHtml($request->input('markdown'));

            return new \App\Http\Resources\ConversionResource([
                'success' => true,
                'data' => [
                    'html' => $html,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                (new \App\Http\Resources\ConversionResource([
                    'success' => false,
                    'message' => 'Failed to convert markdown to HTML',
                    'error' => $e->getMessage(),
                ]))->toArray(request()),
                422
            );
        }
    }

    /**
     * Convert HTML to Markdown
     *
     * Converts HTML content to Markdown format with controlled tag support.
     * Only allowed HTML tags will be processed, others will be stripped.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "markdown": "**Bold text** and *italic text*"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "html": ["The HTML contains unsupported tags: script, iframe"]
     *   }
     * }
     */
    public function htmlToMarkdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'html' => [
                'required',
                'string',
                'max:65535',
                function ($attribute, $value, $fail) {
                    try {
                        $this->markdownService->validateHtml($value);
                    } catch (ValidationException $e) {
                        $errors = $e->validator->errors()->all();
                        foreach ($errors as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                (new \App\Http\Resources\ConversionResource([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]))->toArray(request()),
                422
            );
        }

        try {
            $markdown = $this->markdownService->htmlToMarkdown($request->input('html'));

            return new \App\Http\Resources\ConversionResource([
                'success' => true,
                'data' => [
                    'markdown' => $markdown,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                (new \App\Http\Resources\ConversionResource([
                    'success' => false,
                    'message' => 'Failed to convert HTML to markdown',
                    'error' => $e->getMessage(),
                ]))->toArray(request()),
                422
            );
        }
    }

    /**
     * Validate Markdown content
     *
     * Validates Markdown content without converting it, useful for form validation.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "valid": true,
     *     "message": "Markdown content is valid"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "markdown": ["The markdown contains unbalanced brackets or parentheses."]
     *   }
     * }
     */
    public function validateMarkdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'markdown' => ['required', 'string', new MarkdownRule],
        ]);

        if ($validator->fails()) {
            return response()->json(
                (new \App\Http\Resources\ConversionResource([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]))->toArray(request()),
                422
            );
        }

        return new \App\Http\Resources\ConversionResource([
            'success' => true,
            'data' => [
                'valid' => true,
                'message' => 'Markdown content is valid',
            ],
        ]);
    }

    /**
     * Get allowed HTML tags
     *
     * Returns the list of HTML tags that are supported for HTML-to-Markdown conversion.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "allowed_html_tags": ["p", "br", "div", "span", "h1", "h2", "h3", "h4", "h5", "h6", "strong", "b", "em", "i", "u", "ul", "ol", "li", "a", "img", "table", "thead", "tbody", "tr", "th", "td", "blockquote", "code", "pre", "hr"],
     *     "allowed_markdown_elements": ["headers", "emphasis", "lists", "links", "images", "tables", "blockquotes", "code", "horizontal_rules"]
     *   }
     * }
     */
    public function getAllowedElements()
    {
        return new \App\Http\Resources\ConversionResource([
            'success' => true,
            'data' => [
                'allowed_html_tags' => $this->markdownService->getAllowedHtmlTags(),
                'allowed_markdown_elements' => $this->markdownService->getAllowedMarkdownElements(),
            ],
        ]);
    }

    /**
     * Preview Markdown content
     *
     * Generates an HTML preview of Markdown content for display purposes.
     * This is essentially the same as markdownToHtml but with a different semantic meaning.
     */
    public function previewMarkdown(Request $request)
    {
        return $this->markdownToHtml($request);
    }

    /**
     * Check if content is Markdown
     *
     * Analyzes content to determine if it appears to contain Markdown formatting.
     * Useful for automatic detection of content type.
     *
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "is_markdown": true,
     *     "confidence": "high"
     *   }
     * }
     */
    public function isMarkdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(
                (new \App\Http\Resources\ConversionResource([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ]))->toArray(request()),
                422
            );
        }

        $content = $request->input('content');
        $isMarkdown = $this->markdownService->isMarkdown($content);

        return new \App\Http\Resources\ConversionResource([
            'success' => true,
            'data' => [
                'is_markdown' => $isMarkdown,
                'confidence' => $isMarkdown ? 'high' : 'low',
            ],
        ]);
    }
}
