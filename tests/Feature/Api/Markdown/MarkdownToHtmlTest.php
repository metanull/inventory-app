<?php

namespace Tests\Feature\Api\Markdown;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Tests for Markdown to HTML conversion endpoint
 */
class MarkdownToHtmlTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_converts_basic_markdown_to_html(): void
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '# Heading\n\n**Bold** and *italic* text.',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $html = $response->json('data.html');
        $this->assertStringContainsString('<h1>Heading</h1>', $html);
        $this->assertStringContainsString('<strong>Bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
    }

    public function test_converts_markdown_lists_to_html(): void
    {
        $markdown = "- Item 1\n- Item 2\n\n1. Numbered 1\n2. Numbered 2";

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>Item 1</li>', $html);
        $this->assertStringContainsString('<ol>', $html);
        $this->assertStringContainsString('<li>Numbered 1</li>', $html);
    }

    public function test_converts_markdown_tables_to_html(): void
    {
        $markdown = "| Col 1 | Col 2 |\n|-------|-------|\n| A     | B     |";

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<th>Col 1</th>', $html);
        $this->assertStringContainsString('<td>A</td>', $html);
    }

    public function test_converts_markdown_links_to_html(): void
    {
        $markdown = '[Link text](https://example.com)';

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<a href="https://example.com">Link text</a>', $html);
    }

    public function test_converts_markdown_code_blocks_to_html(): void
    {
        $markdown = "```php\n<?php echo 'Hello'; ?>\n```";

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<pre><code', $html);
        $this->assertStringContainsString('&lt;?php echo \'Hello\'; ?&gt;', $html);
    }

    public function test_rejects_empty_markdown(): void
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['markdown']);
    }

    public function test_rejects_missing_markdown_field(): void
    {
        $response = $this->postJson(route('markdown.toHtml'), []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['markdown']);
    }

    public function test_rejects_non_string_markdown(): void
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => 123,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['markdown']);
    }

    public function test_accepts_previously_unsafe_markdown_content(): void
    {
        // This content was previously rejected by empirical validation but should now be accepted
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '[Click here](javascript:alert("XSS"))',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // The service should process the content and convert it to HTML
        // Note: CommonMark library still strips unsafe links for security (which is good)
        $html = $response->json('data.html');
        $this->assertStringContainsString('<a>Click here</a>', $html);
    }

    public function test_rejects_excessively_large_markdown(): void
    {
        $largeMarkdown = str_repeat('a', 70000); // Exceeds TEXT field limit

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $largeMarkdown,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['markdown']);
    }

    public function test_handles_markdown_with_blockquotes(): void
    {
        $markdown = "> This is a blockquote\n> With multiple lines";

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringContainsString('This is a blockquote', $html);
    }

    public function test_handles_markdown_with_horizontal_rules(): void
    {
        $markdown = "Before\n\n---\n\nAfter";

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<hr', $html);
    }

    public function test_strips_html_from_markdown_input(): void
    {
        $markdown = '# Heading\n\n<script>alert("xss")</script>**Bold text**';

        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => $markdown,
        ]);

        $response->assertOk();
        $html = $response->json('data.html');

        $this->assertStringContainsString('<h1>Heading</h1>', $html);
        $this->assertStringContainsString('<strong>Bold text</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('alert', $html);
    }
}
