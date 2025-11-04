<?php

namespace App\Livewire;

use App\Services\MarkdownService;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class MarkdownEditor extends Component
{
    #[Modelable]
    public string $content = '';

    public string $mode = 'edit'; // 'edit' or 'preview'

    public bool $showHelp = false;

    public string $componentName = 'description';

    public string $label = 'Description';

    public bool $required = false;

    public ?string $helpText = null;

    public int $rows = 8;

    public function mount(
        ?string $initialContent = null,
        string $componentName = 'description',
        string $label = 'Description',
        bool $required = false,
        ?string $helpText = null,
        int $rows = 8
    ): void {
        $this->content = $initialContent ?? '';
        $this->componentName = $componentName;
        $this->label = $label;
        $this->required = $required;
        $this->helpText = $helpText;
        $this->rows = $rows;
    }

    public function switchToEdit(): void
    {
        $this->mode = 'edit';
    }

    public function switchToPreview(): void
    {
        $this->mode = 'preview';
    }

    public function toggleHelp(): void
    {
        $this->showHelp = ! $this->showHelp;
    }

    public function getPreviewProperty(): string
    {
        if (empty($this->content)) {
            return '<span class="text-gray-500 italic">Preview will appear here as you type...</span>';
        }

        return app(MarkdownService::class)->markdownToHtml($this->content);
    }

    public function render()
    {
        return view('livewire.markdown-editor');
    }
}
