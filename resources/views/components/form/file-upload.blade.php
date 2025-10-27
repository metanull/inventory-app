@props([
    'name' => 'file',
    'label' => 'Select File',
    'accept' => 'image/*',
    'required' => false,
    'entity' => null,
    'maxSize' => null,
    'helpText' => null,
])

@php
    if ($entity) {
        $c = $entityColor($entity);
    } else {
        $c = [
            'text' => 'text-indigo-600',
            'focus' => 'focus:ring-indigo-500',
            'border' => 'border-indigo-500',
            'bg' => 'bg-indigo-50',
            'name' => 'indigo'
        ];
    }
    $maxSizeText = $maxSize ? ($maxSize / 1024) . 'MB' : '10MB';
    $help = $helpText ?? "PNG, JPG, GIF up to {$maxSizeText}";
@endphp

<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">
        {{ $label }}@if($required)<span class="text-red-500">*</span>@endif
    </label>
    <label 
        for="{{ $name }}-upload"
        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors cursor-pointer"
        x-data="fileUpload()"
        @drop.prevent="handleDrop($event)"
        @dragover.prevent="highlight()"
        @dragenter.prevent="highlight()"
        @dragleave.prevent="unhighlight()"
        :class="{ 'border-{{ $c['name'] }}-500 bg-{{ $c['name'] }}-50': isDragging }"
    >
        <div class="space-y-1 text-center pointer-events-none">
            <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
            <div class="flex text-sm text-gray-600 justify-center">
                <span class="font-medium {{ $c['text'] }}">Upload a file</span>
                <span class="pl-1">or drag and drop</span>
            </div>
            <p class="text-xs text-gray-500">{{ $help }}</p>
            <p x-show="selectedFile" x-text="'Selected: ' + selectedFile" class="text-sm {{ $c['text'] }} font-medium mt-2"></p>
        </div>
        <input 
            id="{{ $name }}-upload" 
            name="{{ $name }}" 
            type="file" 
            accept="{{ $accept }}" 
            @if($required) required @endif
            class="sr-only"
            @change="handleFileSelect($event)"
        >
    </label>
    @error($name)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@push('scripts')
<script>
function fileUpload() {
    return {
        isDragging: false,
        selectedFile: '',
        
        highlight() {
            this.isDragging = true;
        },
        
        unhighlight() {
            this.isDragging = false;
        },
        
        handleDrop(e) {
            this.isDragging = false;
            const files = e.dataTransfer.files;
            
            if (files.length > 0) {
                const file = files[0];
                const input = this.$el.querySelector('input[type="file"]');
                
                // Check if file matches accept pattern
                const accept = input.getAttribute('accept');
                if (accept && !this.matchesAcceptPattern(file, accept)) {
                    alert('Please upload a valid file type');
                    return;
                }
                
                // Create a new FileList-like object
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                
                // Update the selected file display
                this.selectedFile = file.name;
                
                // Trigger change event so forms recognize the file
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        },
        
        handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.selectedFile = files[0].name;
            } else {
                this.selectedFile = '';
            }
        },
        
        matchesAcceptPattern(file, accept) {
            const patterns = accept.split(',').map(p => p.trim());
            
            for (const pattern of patterns) {
                if (pattern.startsWith('.')) {
                    // Extension match
                    if (file.name.toLowerCase().endsWith(pattern.toLowerCase())) {
                        return true;
                    }
                } else if (pattern.includes('/*')) {
                    // MIME type wildcard match (e.g., image/*)
                    const prefix = pattern.split('/')[0];
                    if (file.type.startsWith(prefix + '/')) {
                        return true;
                    }
                } else if (file.type === pattern) {
                    // Exact MIME type match
                    return true;
                }
            }
            
            return false;
        }
    };
}
</script>
@endpush
