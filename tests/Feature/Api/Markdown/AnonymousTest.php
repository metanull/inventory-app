<?php

namespace Tests\Feature\Api\Markdown;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Anonymous access tests for Markdown API endpoints
 *
 * Tests that all Markdown API endpoints work without authentication
 * since they provide utility functions that don't require user context.
 */
class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // No authentication setup for anonymous tests
    }

    public function test_markdown_to_html_endpoint_works_without_auth(): void
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '# Test Heading\n\n**Bold text**',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'html',
                ],
            ])
            ->assertJsonPath('success', true);

        $html = $response->json('data.html');
        $this->assertStringContainsString('<h1>Test Heading</h1>', $html);
        $this->assertStringContainsString('<strong>Bold text</strong>', $html);
    }

    public function test_html_to_markdown_endpoint_works_without_auth(): void
    {
        $response = $this->postJson(route('markdown.fromHtml'), [
            'html' => '<h1>Test Heading</h1><p><strong>Bold text</strong></p>',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'markdown',
                ],
            ])
            ->assertJsonPath('success', true);

        $markdown = $response->json('data.markdown');
        $this->assertStringContainsString('# Test Heading', $markdown);
        $this->assertStringContainsString('**Bold text**', $markdown);
    }

    public function test_validate_markdown_endpoint_works_without_auth(): void
    {
        $response = $this->postJson(route('markdown.validate'), [
            'markdown' => '# Valid Markdown\n\n**Bold text**',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'valid',
                    'message',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.valid', true);
    }

    public function test_preview_markdown_endpoint_works_without_auth(): void
    {
        $response = $this->postJson(route('markdown.preview'), [
            'markdown' => '# Preview Test\n\n*Italic text*',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'html',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_is_markdown_endpoint_works_without_auth(): void
    {
        $response = $this->postJson(route('markdown.isMarkdown'), [
            'content' => '# This looks like markdown',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'is_markdown',
                    'confidence',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_markdown', true);
    }

    public function test_allowed_elements_endpoint_works_without_auth(): void
    {
        $response = $this->getJson(route('markdown.allowedElements'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'allowed_html_tags',
                    'allowed_markdown_elements',
                ],
            ])
            ->assertJsonPath('success', true);

        $allowedTags = $response->json('data.allowed_html_tags');
        $this->assertIsArray($allowedTags);
        $this->assertContains('p', $allowedTags);
        $this->assertContains('strong', $allowedTags);
    }
}
