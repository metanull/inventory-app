<?php

namespace Tests\Web\Livewire;

use App\Livewire\MarkdownEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarkdownEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->assertOk();
    }

    public function test_component_initializes_with_empty_content(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->assertSet('content', '')
            ->assertSet('mode', 'edit')
            ->assertSet('showHelp', false);
    }

    public function test_component_loads_initial_content(): void
    {
        $content = '# Test Heading';

        Livewire::test(MarkdownEditor::class, [
            'initialContent' => $content,
        ])
            ->assertSet('content', $content);
    }

    public function test_can_switch_to_preview_mode(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->assertSet('mode', 'edit')
            ->call('switchToPreview')
            ->assertSet('mode', 'preview');
    }

    public function test_can_switch_to_edit_mode(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->set('mode', 'preview')
            ->call('switchToEdit')
            ->assertSet('mode', 'edit');
    }

    public function test_can_toggle_help(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->assertSet('showHelp', false)
            ->call('toggleHelp')
            ->assertSet('showHelp', true)
            ->call('toggleHelp')
            ->assertSet('showHelp', false);
    }

    public function test_preview_renders_markdown_as_html(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'initialContent' => '# Heading',
        ])
            ->set('mode', 'preview')
            ->assertSee('<h1>Heading</h1>', false);
    }

    public function test_preview_shows_placeholder_for_empty_content(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->set('mode', 'preview')
            ->assertSee('Preview will appear here as you type');
    }

    public function test_content_updates_reactively(): void
    {
        Livewire::test(MarkdownEditor::class)
            ->set('content', '**Bold**')
            ->assertSet('content', '**Bold**')
            ->set('mode', 'preview')
            ->assertSee('<strong>Bold</strong>', false);
    }

    public function test_preview_updates_when_content_changes(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'initialContent' => '# Original',
        ])
            ->set('mode', 'preview')
            ->assertSee('<h1>Original</h1>', false)
            ->set('content', '# Updated')
            ->assertSee('<h1>Updated</h1>', false)
            ->assertDontSee('<h1>Original</h1>', false);
    }

    public function test_component_accepts_custom_component_name(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'componentName' => 'remarks',
        ])
            ->assertSet('componentName', 'remarks');
    }

    public function test_component_accepts_custom_label(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'label' => 'Custom Label',
        ])
            ->assertSet('label', 'Custom Label')
            ->assertSee('Custom Label');
    }

    public function test_component_can_be_required(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'required' => true,
        ])
            ->assertSet('required', true)
            ->assertSee('required', false);
    }

    public function test_component_shows_help_text_when_provided(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'helpText' => 'Custom help text',
        ])
            ->assertSet('helpText', 'Custom help text')
            ->assertSee('Custom help text');
    }

    public function test_component_respects_custom_rows(): void
    {
        Livewire::test(MarkdownEditor::class, [
            'rows' => 10,
        ])
            ->assertSet('rows', 10)
            ->assertSee('rows="10"', false);
    }

    public function test_markdown_with_code_blocks(): void
    {
        $markdown = "```php\necho 'Hello';\n```";

        Livewire::test(MarkdownEditor::class, [
            'initialContent' => $markdown,
        ])
            ->set('mode', 'preview')
            ->assertSee('<pre', false)
            ->assertSee('echo', false);
    }

    public function test_markdown_with_lists(): void
    {
        $markdown = "- Item 1\n- Item 2\n- Item 3";

        Livewire::test(MarkdownEditor::class, [
            'initialContent' => $markdown,
        ])
            ->set('mode', 'preview')
            ->assertSee('<ul', false)
            ->assertSee('Item 1')
            ->assertSee('Item 2');
    }

    public function test_markdown_with_links(): void
    {
        $markdown = '[GitHub](https://github.com)';

        Livewire::test(MarkdownEditor::class, [
            'initialContent' => $markdown,
        ])
            ->set('mode', 'preview')
            ->assertSee('<a', false)
            ->assertSee('GitHub')
            ->assertSee('https://github.com', false);
    }
}
