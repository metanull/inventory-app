<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Markdown\HtmlToMarkdownRequest;
use App\Http\Requests\Markdown\IsMarkdownRequest;
use App\Http\Requests\Markdown\MarkdownToHtmlRequest;
use App\Http\Requests\Markdown\ValidateMarkdownRequest;
use App\Services\MarkdownService;
use Illuminate\Http\JsonResponse;

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
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "html": "<p><strong>Bold text</strong> and <em>italic text</em></p>"
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
    public function markdownToHtml(MarkdownToHtmlRequest $request): JsonResponse
    {
        try {
            $html = $this->markdownService->markdownToHtml($request->input('markdown'));

            return response()->json([
                'success' => true,
                'data' => [
                    'html' => $html,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert markdown to HTML',
                'error' => $e->getMessage(),
            ], 500);
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
    public function htmlToMarkdown(HtmlToMarkdownRequest $request): JsonResponse
    {
        try {
            $markdown = $this->markdownService->htmlToMarkdown($request->input('html'));

            return response()->json([
                'success' => true,
                'data' => [
                    'markdown' => $markdown,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert HTML to markdown',
                'error' => $e->getMessage(),
            ], 500);
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
    public function validateMarkdown(ValidateMarkdownRequest $request): JsonResponse
    {
        return response()->json([
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
    public function getAllowedElements(): JsonResponse
    {
        return response()->json([
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
    public function previewMarkdown(MarkdownToHtmlRequest $request): JsonResponse
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
    public function isMarkdown(IsMarkdownRequest $request): JsonResponse
    {
        $content = $request->input('content');
        $isMarkdown = $this->markdownService->isMarkdown($content);

        return response()->json([
            'success' => true,
            'data' => [
                'is_markdown' => $isMarkdown,
                'confidence' => $isMarkdown ? 'high' : 'low',
            ],
        ]);
    }
}
