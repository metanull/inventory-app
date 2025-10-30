@props([
    'name' => 'description',
    'label' => 'Description',
    'value' => null,
    'rows' => 8,
    'required' => false,
    'helpText' => null,
])

<div x-data="markdownEditor({
    value: @js(old($name, $value ?? '')),
    debounceMs: 300
})"
    class="space-y-2"
    x-cloak>
    
    <!-- Header with Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200">
        <button 
            type="button"
            @click="mode = 'edit'"
            :class="{
                'border-b-2 border-indigo-600 text-indigo-600': mode === 'edit',
                'text-gray-500 hover:text-gray-700': mode !== 'edit'
            }"
            class="px-4 py-2 font-medium text-sm transition"
        >
            ✏️ Edit
        </button>
        <button 
            type="button"
            @click="mode = 'preview'"
            :class="{
                'border-b-2 border-indigo-600 text-indigo-600': mode === 'preview',
                'text-gray-500 hover:text-gray-700': mode !== 'preview'
            }"
            class="px-4 py-2 font-medium text-sm transition"
        >
            👁️ Preview
        </button>
        <button 
            type="button"
            @click="showHelp = !showHelp"
            :class="{ 'text-indigo-600': showHelp }"
            class="ml-auto px-4 py-2 font-medium text-sm text-gray-500 hover:text-gray-700 transition"
        >
            ❓ Help
        </button>
    </div>
    
    <!-- Editor Section -->
    <div x-show="mode === 'edit'" class="space-y-2">
        @if($required)
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
                {{ $label }} <span class="text-red-500">*</span>
            </label>
        @else
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
                {{ $label }}
            </label>
        @endif
        <textarea 
            id="{{ $name }}"
            name="{{ $name }}"
            x-model.debounce-300ms="value"
            rows="{{ $rows }}"
            {{ $required ? 'required' : '' }}
            placeholder="Enter Markdown text here... Use **bold**, *italic*, # headings, - lists, etc."
            class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        ></textarea>
    </div>
    
    <!-- Preview Section -->
    <div x-show="mode === 'preview'" class="border border-gray-300 rounded-md p-4 min-h-40 bg-gray-50">
        <div 
            x-html="preview"
            class="prose prose-sm max-w-none [&_h1]:text-2xl [&_h2]:text-xl [&_h3]:text-lg [&_h4]:text-base [&_code]:bg-gray-200 [&_code]:px-1 [&_code]:py-0.5 [&_code]:rounded [&_pre]:bg-gray-900 [&_pre]:text-gray-100 [&_pre]:p-3 [&_pre]:rounded [&_pre]:overflow-x-auto [&_pre_code]:bg-transparent [&_pre_code]:text-inherit"
        ></div>
        <div x-show="!value" class="text-gray-500 text-sm italic">
            Preview will appear here as you type...
        </div>
    </div>
    
    <!-- Help Section -->
    <div x-show="showHelp" class="bg-blue-50 border border-blue-200 rounded-md p-4 text-sm space-y-3">
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
                <dt class="font-mono text-xs bg-white px-2 py-1 rounded border border-gray-200">| Col1 | Col2 |</dt>
                <dd class="text-blue-900 text-xs">Tables (GitHub Flavored Markdown)</dd>
            </div>
        </dl>
    </div>
    
    <!-- Error Display -->
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    
    <!-- Help Text -->
    @if($helpText)
        <p class="text-xs text-gray-500">{{ $helpText }}</p>
    @endif
</div>

<script>
// Alpine component for Markdown Editor
function markdownEditor({ value = '', debounceMs = 300 }) {
    return {
        value: value,
        mode: 'edit',
        showHelp: false,
        debounceMs: debounceMs,
        debounceTimer: null,
        preview: '',
        
        init() {
            // Initial preview
            this.updatePreview();
            
            // Watch for changes
            this.$watch('value', () => {
                this.updatePreview();
            });
        },
        
        updatePreview() {
            if (typeof window.marked !== 'undefined' && this.value) {
                try {
                    this.preview = window.marked.parse(this.value);
                } catch (error) {
                    console.error('Markdown parsing error:', error);
                    this.preview = '<p class="text-red-500">Error parsing Markdown</p>';
                }
            } else {
                this.preview = '';
            }
        }
    };
}
</script>
