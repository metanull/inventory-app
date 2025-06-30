<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Service for handling Markdown conversion and validation
 *
 * This service provides functionality to:
 * - Convert Markdown to HTML with security and formatting controls
 * - Convert HTML to Markdown with controlled tag support
 * - Validate Markdown content for proper formatting
 * - Sanitize and control allowed HTML tags during conversion
 */
class MarkdownService
{
    private MarkdownConverter $markdownConverter;

    private HtmlConverter $htmlConverter;

    private array $allowedHtmlTags;

    private array $allowedMarkdownElements;

    public function __construct()
    {
        $this->initializeMarkdownConverter();
        $this->initializeHtmlConverter();
        $this->setAllowedElements();
    }

    /**
     * Initialize the Markdown to HTML converter with security and formatting extensions
     */
    private function initializeMarkdownConverter(): void
    {
        $config = [
            'html_input' => 'strip', // Strip all HTML tags for security
            'allow_unsafe_links' => false, // Prevent javascript: and data: URLs
            'max_nesting_level' => 10, // Prevent deeply nested structures
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new TableExtension);

        $this->markdownConverter = new MarkdownConverter($environment);
    }

    /**
     * Initialize the HTML to Markdown converter with controlled tag support
     */
    private function initializeHtmlConverter(): void
    {
        $this->htmlConverter = new HtmlConverter([
            'header_style' => 'atx', // Use # for headers
            'bold_style' => '**', // Use ** for bold
            'italic_style' => '*', // Use * for italic
            'list_item_style' => '-', // Use - for unordered lists
            'preserve_comments' => false, // Remove HTML comments
            'strip_tags' => false, // Don't strip tags, convert them
            'remove_nodes' => 'script style', // Remove dangerous elements
            'hard_break' => true, // Convert <br> to line breaks
            'table_pipe_escape' => '\\|', // Escape pipes in table content
        ]);

        // Add custom table converters
        $this->addTableConverters();
    }

    /**
     * Add custom converters for table elements
     */
    private function addTableConverters(): void
    {
        $environment = $this->htmlConverter->getEnvironment();

        // Table converter
        $environment->addConverter(new \League\HTMLToMarkdown\Converter\TableConverter);
    }

    /**
     * Set allowed HTML tags and Markdown elements for validation
     */
    private function setAllowedElements(): void
    {
        // Allowed HTML tags for HTML-to-Markdown conversion
        $this->allowedHtmlTags = [
            'p', 'br', 'div', 'span',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'strong', 'b', 'em', 'i', 'u',
            'ul', 'ol', 'li',
            'a', 'img',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'blockquote', 'code', 'pre',
            'hr',
        ];

        // Allowed Markdown elements for validation
        $this->allowedMarkdownElements = [
            'headers', 'emphasis', 'lists', 'links', 'images',
            'tables', 'blockquotes', 'code', 'horizontal_rules',
        ];
    }

    /**
     * Convert Markdown to HTML
     *
     * @param  string  $markdown  The Markdown content to convert
     * @return string The converted HTML content
     *
     * @throws ValidationException If the Markdown is invalid
     */
    public function markdownToHtml(string $markdown): string
    {
        $this->validateMarkdown($markdown);

        // Normalize line endings to ensure proper parsing
        $normalizedMarkdown = str_replace(['\n', '\r\n', '\r'], ["\n", "\n", "\n"], $markdown);

        // Only preprocess if there are HTML tags present outside of code blocks
        if ($this->containsHtmlOutsideCodeBlocks($normalizedMarkdown)) {
            // Remove script tags and their content completely before processing
            $normalizedMarkdown = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $normalizedMarkdown);
            $normalizedMarkdown = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $normalizedMarkdown);

            // Remove other HTML tags but preserve spacing, avoiding code blocks
            $normalizedMarkdown = $this->removeHtmlTagsOutsideCodeBlocks($normalizedMarkdown);

            // Clean up excessive whitespace but be careful with markdown structure
            $normalizedMarkdown = preg_replace('/[ \t]+/', ' ', $normalizedMarkdown); // Only horizontal whitespace
            $normalizedMarkdown = preg_replace('/\n +/', "\n", $normalizedMarkdown); // Remove leading spaces on lines
            $normalizedMarkdown = preg_replace('/ +\n/', "\n", $normalizedMarkdown); // Remove trailing spaces on lines
        }

        return $this->markdownConverter->convert($normalizedMarkdown)->getContent();
    }

    /**
     * Check if HTML tags exist outside of code blocks
     */
    private function containsHtmlOutsideCodeBlocks(string $markdown): bool
    {
        // Remove code blocks temporarily
        $withoutCodeBlocks = preg_replace('/```[\s\S]*?```/', '', $markdown);
        $withoutInlineCode = preg_replace('/`[^`]*`/', '', $withoutCodeBlocks);

        return preg_match('/<[^>]+>/', $withoutInlineCode);
    }

    /**
     * Remove HTML tags outside of code blocks
     */
    private function removeHtmlTagsOutsideCodeBlocks(string $markdown): string
    {
        // Split on code blocks to preserve them
        $parts = preg_split('/```[\s\S]*?```/', $markdown, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE);

        $result = '';
        for ($i = 0; $i < count($parts); $i++) {
            if ($i % 2 === 0) {
                // This is content outside code blocks - remove HTML tags
                $result .= preg_replace('/<[^>]+>/', ' ', $parts[$i][0]);
            } else {
                // This is a code block - preserve as is
                $result .= $parts[$i][0];
            }
        }

        return $result;
    }

    /**
     * Convert HTML snippet to Markdown
     *
     * @param  string  $html  The HTML content to convert
     * @return string The converted Markdown content
     *
     * @throws ValidationException If the HTML contains unsupported tags
     */
    public function htmlToMarkdown(string $html): string
    {
        $this->validateHtml($html);

        // Clean up the HTML before conversion
        $cleanHtml = $this->sanitizeHtml($html);

        return $this->htmlConverter->convert($cleanHtml);
    }

    /**
     * Validate Markdown content for proper formatting
     *
     * @param  string  $markdown  The Markdown content to validate
     *
     * @throws ValidationException If validation fails
     */
    public function validateMarkdown(string $markdown): void
    {
        $validator = Validator::make(['markdown' => $markdown], [
            'markdown' => [
                'required',
                'string',
                'max:65535', // TEXT field limit
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate HTML content for allowed tags
     *
     * @param  string  $html  The HTML content to validate
     *
     * @throws ValidationException If validation fails
     */
    public function validateHtml(string $html): void
    {
        $validator = Validator::make(['html' => $html], [
            'html' => [
                'required',
                'string',
                'max:65535', // TEXT field limit
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Sanitize HTML by removing dangerous attributes and elements
     *
     * @param  string  $html  The HTML to sanitize
     * @return string The sanitized HTML
     */
    private function sanitizeHtml(string $html): string
    {
        // Remove dangerous attributes
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*class\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Remove script and style tags completely
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);

        // Clean up excessive whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        $html = trim($html);

        return $html;
    }

    /**
     * Get allowed HTML tags for reference
     *
     * @return array List of allowed HTML tags
     */
    public function getAllowedHtmlTags(): array
    {
        return $this->allowedHtmlTags;
    }

    /**
     * Get allowed Markdown elements for reference
     *
     * @return array List of allowed Markdown elements
     */
    public function getAllowedMarkdownElements(): array
    {
        return $this->allowedMarkdownElements;
    }

    /**
     * Preview Markdown content (convert to HTML for display)
     *
     * @param  string  $markdown  The Markdown content to preview
     * @return string The HTML preview
     */
    public function previewMarkdown(string $markdown): string
    {
        return $this->markdownToHtml($markdown);
    }

    /**
     * Check if a string contains Markdown formatting
     *
     * @param  string  $content  The content to check
     * @return bool True if content appears to contain Markdown
     */
    public function isMarkdown(string $content): bool
    {
        // Check for common Markdown patterns
        $patterns = [
            '/^#{1,6}\s/m', // Headers (with multiline flag)
            '/\*\*.*\*\*/', // Bold
            '/\*.*\*/', // Italic
            '/^[\*\-]\s/m', // Unordered list (with multiline flag, support both * and -)
            '/^\d+\.\s/m', // Ordered list (with multiline flag)
            '/\[.*\]\(.*\)/', // Links
            '/!\[.*\]\(.*\)/', // Images
            '/^>\s/m', // Blockquotes (with multiline flag)
            '/`.*`/', // Inline code
            '/^```/m', // Code blocks (with multiline flag)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}
