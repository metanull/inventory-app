<div class="space-y-2">
    <!-- Header with Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200">
        <button 
            type="button"
            wire:click="switchToEdit"
            class="px-4 py-2 font-medium text-sm transition {{ $mode === 'edit' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}"
        >
            ✏️ Edit
        </button>
        <button 
            type="button"
            wire:click="switchToPreview"
            class="px-4 py-2 font-medium text-sm transition {{ $mode === 'preview' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}"
        >
            👁️ Preview
        </button>
        <button 
            type="button"
            wire:click="toggleHelp"
            class="ml-auto px-4 py-2 font-medium text-sm transition {{ $showHelp ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}"
        >
            ❓ Help
        </button>
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
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">**bold**</dt>
                    <dd class="text-blue-900 text-xs">Bold text</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">*italic*</dt>
                    <dd class="text-blue-900 text-xs">Italic text</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200"># Heading</dt>
                    <dd class="text-blue-900 text-xs">Heading level 1</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">## Heading 2</dt>
                    <dd class="text-blue-900 text-xs">Heading level 2</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">- Item</dt>
                    <dd class="text-blue-900 text-xs">Bullet list</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">1. Item</dt>
                    <dd class="text-blue-900 text-xs">Numbered list</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">[text](url)</dt>
                    <dd class="text-blue-900 text-xs">Link</dd>
                </div>
                <div>
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">```code```</dt>
                    <dd class="text-blue-900 text-xs">Code block</dd>
                </div>
                <div class="col-span-2">
                    <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">| Col1 | Col2 |<br>|------|------|</dt>
                    <dd class="text-blue-900 text-xs">Tables (GitHub Flavored Markdown)</dd>
                </div>
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
