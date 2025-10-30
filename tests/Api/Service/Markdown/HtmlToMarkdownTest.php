<?php

namespace Tests\Api\Service\Markdown;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for HTML to Markdown conversion endpoint
 */
class HtmlToMarkdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_converts_basic_html_to_markdown(): void
    {
        $html = '<h1>Heading</h1><p><strong>Bold</strong> and <em>italic</em> text.</p>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $markdown = $response->json('data.markdown');
        $this->assertStringContainsString('# Heading', $markdown);
        $this->assertStringContainsString('**Bold**', $markdown);
        $this->assertStringContainsString('*italic*', $markdown);
    }

    public function test_converts_html_lists_to_markdown(): void
    {
        $html = '<ul><li>Item 1</li><li>Item 2</li></ul><ol><li>Numbered 1</li><li>Numbered 2</li></ol>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk();
        $markdown = $response->json('data.markdown');

        $this->assertStringContainsString('- Item 1', $markdown);
        $this->assertStringContainsString('1. Numbered 1', $markdown);
    }

    public function test_converts_html_tables_to_markdown(): void
    {
        $html = '<table><thead><tr><th>Col 1</th><th>Col 2</th></tr></thead><tbody><tr><td>A</td><td>B</td></tr></tbody></table>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk();
        $markdown = $response->json('data.markdown');

        $this->assertStringContainsString('|', $markdown);
        $this->assertStringContainsString('Col 1', $markdown);
        $this->assertStringContainsString('A', $markdown);
    }

    public function test_converts_html_links_to_markdown(): void
    {
        $html = '<p><a href="https://example.com">Link text</a></p>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk();
        $markdown = $response->json('data.markdown');

        $this->assertStringContainsString('[Link text](https://example.com)', $markdown);
    }

    public function test_converts_html_code_to_markdown(): void
    {
        $html = '<pre><code>echo "Hello World";</code></pre>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk();
        $markdown = $response->json('data.markdown');

        $this->assertStringContainsString('```', $markdown);
        $this->assertStringContainsString('echo "Hello World";', $markdown);
    }

    public function test_converts_html_blockquotes_to_markdown(): void
    {
        $html = '<blockquote><p>This is a quote</p></blockquote>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk();
        $markdown = $response->json('data.markdown');

        $this->assertStringContainsString('> This is a quote', $markdown);
    }

    public function test_rejects_empty_html(): void
    {
        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['html']);
    }

    public function test_rejects_missing_html_field(): void
    {
        $response = $this->postJson(route('markdown.fromHtml'), []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['html']);
    }

    public function test_rejects_non_string_html(): void
    {
        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => 123,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['html']);
    }

    public function test_rejects_excessively_large_html(): void
    {
        $largeText = str_repeat('a', 70000);
        $largeHtml = "<p>$largeText</p>"; // Exceeds TEXT field limit

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $largeHtml,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['html']);
    }

    public function test_accepts_all_supported_html_tags(): void
    {
        $supportedTags = [
            '<p>Paragraph</p>',
            '<h1>Header 1</h1>',
            '<h2>Header 2</h2>',
            '<strong>Strong</strong>',
            '<em>Emphasis</em>',
            '<ul><li>List item</li></ul>',
            '<a href="https://example.com">Link</a>',
            '<img src="image.jpg" alt="Image">',
            '<blockquote>Quote</blockquote>',
            '<code>Code</code>',
            '<hr>',
        ];

        foreach ($supportedTags as $html) {
            $response = $this->postJson(route('markdown.fromHtml'), [
                'html' => $html,
            ]);

            $response->assertOk(
                "Failed for HTML: {$html}. Response: ".$response->getContent()
            );
        }
    }

    public function test_handles_nested_html_elements(): void
    {
        $html = '<div><h1>Title</h1><p><strong>Bold</strong> text with <em>nested <strong>bold emphasis</strong></em></p></div>';

        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => $html,
        ]);

        $response->assertOk();
        $markdown = $response->json('data.markdown');

        $this->assertStringContainsString('# Title', $markdown);
        $this->assertStringContainsString('**Bold**', $markdown);
    }
}
