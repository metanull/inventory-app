<?php

namespace App\Traits;

use App\Services\MarkdownService;

/**
 * Trait for models that have Markdown content fields
 *
 * This trait provides methods to handle Markdown content in model attributes,
 * including conversion to HTML for display and validation.
 */
trait HasMarkdownFields
{
    /**
     * Convert a Markdown field to HTML
     *
     * @param  string  $field  The field name containing Markdown
     * @return string The HTML representation
     */
    public function getMarkdownAsHtml(string $field): string
    {
        $markdownService = app(MarkdownService::class);
        $markdown = $this->getAttribute($field) ?? '';

        if (empty($markdown)) {
            return '';
        }

        try {
            return $markdownService->markdownToHtml($markdown);
        } catch (\Exception $e) {
            // Fallback to raw content if conversion fails
            return htmlspecialchars($markdown);
        }
    }

    /**
     * Set a field with HTML content converted to Markdown
     *
     * @param  string  $field  The field name to set
     * @param  string  $html  The HTML content to convert
     */
    public function setFieldFromHtml(string $field, string $html): void
    {
        $markdownService = app(MarkdownService::class);

        try {
            $markdown = $markdownService->htmlToMarkdown($html);
            $this->setAttribute($field, $markdown);
        } catch (\Exception $e) {
            // If conversion fails, store as plain text
            $plainText = strip_tags($html);
            $this->setAttribute($field, $plainText);
        }
    }

    /**
     * Check if a field contains Markdown formatting
     *
     * @param  string  $field  The field name to check
     * @return bool True if the field appears to contain Markdown
     */
    public function fieldIsMarkdown(string $field): bool
    {
        $markdownService = app(MarkdownService::class);
        $content = $this->getAttribute($field) ?? '';

        return $markdownService->isMarkdown($content);
    }

    /**
     * Get the list of fields that should be treated as Markdown
     * Override this method in your model to specify which fields contain Markdown
     *
     * @return array List of field names that contain Markdown
     */
    public function getMarkdownFields(): array
    {
        return [
            // Override in your model to specify Markdown fields
            // Example: ['description', 'notes', 'content']
        ];
    }

    /**
     * Accessor to get HTML version of Markdown fields
     * This creates dynamic accessors like 'description_html' for 'description' field
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // Check if this is a request for HTML version of a Markdown field
        if (str_ends_with($key, '_html')) {
            $markdownField = str_replace('_html', '', $key);
            if (in_array($markdownField, $this->getMarkdownFields())) {
                return $this->getMarkdownAsHtml($markdownField);
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Boot the trait
     */
    protected static function bootHasMarkdownFields(): void
    {
        // Add any model events or initialization here if needed
    }
}
