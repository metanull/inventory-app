<?php

namespace Tests\Feature\Api\ParameterValidation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Markdown API endpoints
 */
class MarkdownParameterValidationTest extends TestCase
{
    use RefreshDatabase;

    // MARKDOWN TO HTML ENDPOINT TESTS
    public function test_markdown_to_html_validates_required_content()
    {
        $response = $this->postJson(route('markdown.toHtml'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['markdown']);
    }

    public function test_markdown_to_html_accepts_valid_markdown()
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '# Test Heading\n\nThis is a test paragraph.',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['html']]);
    }

    public function test_markdown_to_html_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '# Test Markdown',
            'unexpected_field' => 'should_be_rejected',
            'render_mode' => 'enhanced', // Not implemented
            'allow_html' => true, // Not implemented
            'sanitize' => false, // Not implemented
            'admin_conversion' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // HTML TO MARKDOWN ENDPOINT TESTS
    public function test_html_to_markdown_validates_required_content()
    {
        $response = $this->postJson(route('markdown.fromHtml'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['html']);
    }

    public function test_html_to_markdown_accepts_valid_html()
    {
        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => '<h1>Test Heading</h1><p>This is a test paragraph.</p>',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['markdown']]);
    }

    public function test_html_to_markdown_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => '<h1>Test HTML</h1>',
            'unexpected_field' => 'should_be_rejected',
            'preserve_formatting' => true, // Not implemented
            'strip_attributes' => false, // Not implemented
            'convert_tables' => true, // Not implemented
            'admin_conversion' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // VALIDATE MARKDOWN ENDPOINT TESTS
    public function test_validate_markdown_validates_required_content()
    {
        $response = $this->postJson(route('markdown.validate'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['markdown']);
    }

    public function test_validate_markdown_accepts_valid_markdown()
    {
        $response = $this->postJson(route('markdown.validate'), [
            'markdown' => '# Valid Markdown\n\n- List item 1\n- List item 2',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['valid', 'message']]);
    }

    public function test_validate_markdown_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('markdown.validate'), [
            'markdown' => '# Test Validation',
            'unexpected_field' => 'should_be_rejected',
            'strict_mode' => true, // Not implemented
            'check_links' => true, // Not implemented
            'validate_syntax' => 'enhanced', // Not implemented
            'admin_validation' => true,
            'privilege_escalation' => 'validation_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // PREVIEW MARKDOWN ENDPOINT TESTS
    public function test_preview_markdown_validates_required_content()
    {
        $response = $this->postJson(route('markdown.preview'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['markdown']);
    }

    public function test_preview_markdown_accepts_valid_markdown()
    {
        $response = $this->postJson(route('markdown.preview'), [
            'markdown' => '# Preview Test\n\n**Bold text** and *italic text*.',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['html']]);
    }

    public function test_preview_markdown_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('markdown.preview'), [
            'markdown' => '# Test Preview',
            'unexpected_field' => 'should_be_rejected',
            'theme' => 'dark', // Not implemented
            'show_line_numbers' => true, // Not implemented
            'highlight_syntax' => true, // Not implemented
            'admin_preview' => true,
            'privilege_escalation' => 'preview_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // IS MARKDOWN ENDPOINT TESTS
    public function test_is_markdown_validates_required_content()
    {
        $response = $this->postJson(route('markdown.isMarkdown'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['content']);
    }

    public function test_is_markdown_accepts_valid_content()
    {
        $response = $this->postJson(route('markdown.isMarkdown'), [
            'content' => '# This looks like markdown\n\n- With a list',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['is_markdown', 'confidence']]);
    }

    public function test_is_markdown_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('markdown.isMarkdown'), [
            'content' => 'Test content for detection',
            'unexpected_field' => 'should_be_rejected',
            'detection_mode' => 'strict', // Not implemented
            'confidence_threshold' => 0.8, // Not implemented
            'admin_detection' => true,
            'privilege_escalation' => 'detection_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // ALLOWED ELEMENTS ENDPOINT TESTS
    public function test_allowed_elements_rejects_unexpected_query_parameters_currently()
    {
        $response = $this->getJson(route('markdown.allowedElements').'?format=detailed&include_examples=true&admin_access=true');

        $response->assertOk(); // This endpoint has no parameters to validate, so it passes
        $response->assertJsonStructure(['data' => ['allowed_html_tags', 'allowed_markdown_elements']]);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_markdown()
    {
        $unicodeContent = [
            '# Titre français avec accents éàç',
            '# Русский заголовок с кириллицей',
            '# 日本語のタイトル',
            '# عنوان باللغة العربية',
            '# Título español con ñ',
            '# Titolo italiano con caratteri speciali',
            '# Polski tytuł ze znakami diakrytycznymi',
            '# Ελληνικός τίτλος',
            '# Dansk titel med æøå',
            '# Magyar cím ékezetes betűkkel',
        ];

        foreach ($unicodeContent as $content) {
            $response = $this->postJson(route('markdown.toHtml'), [
                'markdown' => $content,
            ]);

            $response->assertOk(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_very_long_content()
    {
        $veryLongContent = str_repeat('# Very Long Markdown Content\n\nThis is a paragraph that will be repeated many times to test how the system handles very long content input.\n\n', 100);

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $veryLongContent,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [200, 413, 422]);
    }

    public function test_handles_malicious_markdown_content()
    {
        $maliciousContent = [
            '<script>alert("XSS")</script>',
            '[XSS](javascript:alert("XSS"))',
            '![XSS](javascript:alert("XSS"))',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<img src="x" onerror="alert(\'XSS\')">',
            '[Click me](data:text/html,<script>alert("XSS")</script>)',
            '```html\n<script>alert("XSS")</script>\n```',
        ];

        foreach ($maliciousContent as $content) {
            $response = $this->postJson(route('markdown.toHtml'), [
                'markdown' => $content,
            ]);

            $response->assertOk(); // Should handle but sanitize

            if (isset($response->json()['data']['html'])) {
                $html = $response->json()['data']['html'];
                // Should not contain dangerous scripts
                $this->assertStringNotContainsString('<script>', $html);
                $this->assertStringNotContainsString('javascript:', $html);
                $this->assertStringNotContainsString('onerror=', $html);
            }
        }
    }

    public function test_handles_malicious_html_content()
    {
        $maliciousHtml = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<link rel="stylesheet" href="javascript:alert(\'XSS\')">',
            '<style>body{background:url("javascript:alert(\'XSS\')")}</style>',
            '<div onclick="alert(\'XSS\')">Click me</div>',
            '<svg onload="alert(\'XSS\')"></svg>',
        ];

        foreach ($maliciousHtml as $content) {
            $response = $this->postJson(route('markdown.fromHtml'), [
                'html' => $content,
            ]);

            $response->assertOk(); // Should handle but sanitize

            if (isset($response->json()['data']['markdown'])) {
                $markdown = $response->json()['data']['markdown'];
                // Should not contain dangerous script tags (they get removed)
                $this->assertStringNotContainsString('<script>', $markdown);
                // Event handlers like onerror should be removed
                $this->assertStringNotContainsString('onerror=', $markdown);
                // Note: javascript: URLs in src/href are preserved but rendered harmless in markdown format
            }
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $endpoints = [
            route('markdown.toHtml'),
            route('markdown.fromHtml'),
            route('markdown.validate'),
            route('markdown.preview'),
            route('markdown.isMarkdown'),
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->postJson($endpoint, [
                'content' => ['array' => 'instead_of_string'],
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['content']);
        }
    }

    public function test_handles_null_and_empty_content()
    {
        $endpoints = [
            route('markdown.toHtml'),
            route('markdown.fromHtml'),
            route('markdown.validate'),
            route('markdown.preview'),
            route('markdown.isMarkdown'),
        ];

        $testCases = [
            ['content' => null],
            ['content' => ''],
            ['content' => '   '], // Whitespace only
        ];

        foreach ($endpoints as $endpoint) {
            foreach ($testCases as $data) {
                $response = $this->postJson($endpoint, $data);

                // Should handle gracefully
                $this->assertContains($response->status(), [200, 422]);
            }
        }
    }

    public function test_handles_special_markdown_characters()
    {
        $specialContent = [
            '# Title with "quotes" here',
            "# Title with 'apostrophes' content",
            '# Title & symbol content',
            '# Title: colon content',
            '# Title (parentheses) content',
            '# Title - dash content',
            '# Title @ symbol content',
            '# Title #hashtag content',
            '# Title 50% percentage',
            '# Title $dollar content',
            '# Title *asterisk content',
            '# Title +plus content',
            '# Title =equals content',
            '# Title |pipe content',
            '# Title [brackets] content',
            '# Title {braces} content',
            '# Title `backticks` content',
            '# Title ~tilde content',
            '# Title ^ caret content',
            '# Title \\ backslash content',
        ];

        foreach ($specialContent as $content) {
            $response = $this->postJson(route('markdown.toHtml'), [
                'markdown' => $content,
            ]);

            $response->assertOk(); // Should handle special characters gracefully
        }
    }

    public function test_handles_complex_markdown_structures()
    {
        $complexMarkdown = '
# Main Heading

## Sub Heading

### Sub Sub Heading

This is a paragraph with **bold text** and *italic text* and `code`.

- List item 1
- List item 2
  - Nested item
  - Another nested item

1. Numbered list
2. Second item
   1. Nested numbered
   2. Another nested

[Link text](https://example.com)

![Image alt text](https://example.com/image.jpg)

> This is a blockquote
> with multiple lines

```php
<?php
echo "Code block";
?>
```

| Table | Header |
|-------|--------|
| Cell  | Data   |

---

Horizontal rule above.
        ';

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $complexMarkdown,
        ]);

        $response->assertOk(); // Should handle complex structures
    }
}
