<div class="space-y-2">
    <!-- Header with Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200">
        <x-ui.tab-button wire:click="switchToEdit" :active="$mode === 'edit'">
            ✏️ Edit
        </x-ui.tab-button>
        
        <x-ui.tab-button wire:click="switchToPreview" :active="$mode === 'preview'">
            👁️ Preview
        </x-ui.tab-button>
        
        <x-ui.tab-button wire:click="toggleHelp" :active="$showHelp" class="ml-auto">
            ❓ Help
        </x-ui.tab-button>
    </div>
    
    <!-- Editor Section -->
    @if($mode === 'edit')
        <div class="space-y-2">
            @if($required)
                <label for="{{ $componentName }}" class="block text-sm font-medium text-gray-700">
                    {{ $label }} <span class="text-red-500">*</span>
                </label>
            @else
                <label for="{{ $componentName }}" class="block text-sm font-medium text-gray-700">
                    {{ $label }}
                </label>
            @endif
            
            <textarea 
                id="{{ $componentName }}"
                name="{{ $componentName }}"
                wire:model.live.debounce.300ms="content"
                rows="{{ $rows }}"
                {{ $required ? 'required' : '' }}
                placeholder="Enter Markdown text here... Use **bold**, *italic*, # headings, - lists, etc."
                class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            ></textarea>
        </div>
    @endif
    
    <!-- Preview Section -->
    @if($mode === 'preview')
        <div class="border border-gray-300 rounded-md p-4 min-h-40 bg-gray-50">
            <div class="prose prose-sm max-w-none [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h4]:text-base [&_code]:bg-gray-200 [&_code]:px-1 [&_code]:py-0.5 [&_code]:rounded [&_pre]:bg-gray-900 [&_pre]:text-gray-100 [&_pre]:p-3 [&_pre]:rounded [&_pre]:overflow-x-auto [&_pre_code]:bg-transparent [&_pre_code]:text-inherit">
                {!! $this->preview !!}
            </div>
        </div>
    @endif
    
    <!-- Help Section -->
    @if($showHelp)
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 text-sm space-y-3">
            <h4 class="font-semibold text-blue-900">Markdown Syntax Guide</h4>
            <dl class="grid grid-cols-2 gap-3">
                <x-ui.help-item syntax="**bold**" description="Bold text" />
                <x-ui.help-item syntax="*italic*" description="Italic text" />
                <x-ui.help-item syntax="# Heading" description="Heading level 1" />
                <x-ui.help-item syntax="## Heading 2" description="Heading level 2" />
                <x-ui.help-item syntax="- Item" description="Bullet list" />
                <x-ui.help-item syntax="1. Item" description="Numbered list" />
                <x-ui.help-item syntax="[text](url)" description="Link" />
                <x-ui.help-item syntax="```code```" description="Code block" />
                <x-ui.help-item syntax="| Col1 | Col2 |<br>|------|------|" description="Tables (GitHub Flavored Markdown)" :colspan="true" />
            </dl>
        </div>
    @endif
    
    <!-- Error Display -->
    @error($componentName)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    
    <!-- Help Text -->
    @if($helpText)
        <p class="text-xs text-gray-500">{{ $helpText }}</p>
    @endif
</div>
