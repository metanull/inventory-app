@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select or search...',
    'required' => false,
    'valueField' => 'id',
    'labelField' => 'name',
])

@php
    $inputId = 'input-' . $name;
    $listId = 'list-' . $name;
    $selectedLabel = '';
    
    if ($selected) {
        foreach ($options as $option) {
            $value = is_array($option) ? $option[$valueField] : $option->$valueField;
            if ($value == $selected) {
                $selectedLabel = is_array($option) ? $option[$labelField] : $option->$labelField;
                break;
            }
        }
    }
@endphp

<div class="relative">
    {{-- Hidden input to store the actual value --}}
    <input 
        type="hidden" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        value="{{ old($name, $selected) }}"
    >
    
    {{-- Visible input for search/display --}}
    <input 
        type="text" 
        id="{{ $inputId }}"
        list="{{ $listId }}"
        value="{{ old($name . '_label', $selectedLabel) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        autocomplete="off"
    >
    
    {{-- Datalist for autocomplete --}}
    <datalist id="{{ $listId }}">
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $option)
            @php
                $value = is_array($option) ? $option[$valueField] : $option->$valueField;
                $label = is_array($option) ? $option[$labelField] : $option->$labelField;
            @endphp
            <option value="{{ $label }}" data-value="{{ $value }}">
        @endforeach
    </datalist>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('{{ $inputId }}');
    const hidden = document.getElementById('{{ $name }}');
    const datalist = document.getElementById('{{ $listId }}');
    
    if (input && hidden && datalist) {
        input.addEventListener('input', function() {
            const value = this.value;
            let found = false;
            
            // Try to find matching option
            for (let option of datalist.options) {
                if (option.value === value) {
                    hidden.value = option.getAttribute('data-value') || '';
                    found = true;
                    break;
                }
            }
            
            // If no match found, clear hidden value
            if (!found) {
                hidden.value = '';
            }
        });
        
        // Clear on focus for easier selection
        input.addEventListener('focus', function() {
            if (this.value && hidden.value) {
                // Store current selection
                this.setAttribute('data-last-value', this.value);
            }
        });
        
        // Restore on blur if nothing selected
        input.addEventListener('blur', function() {
            if (!hidden.value && this.getAttribute('data-last-value')) {
                this.value = this.getAttribute('data-last-value');
                // Try to restore hidden value
                for (let option of datalist.options) {
                    if (option.value === this.value) {
                        hidden.value = option.getAttribute('data-value') || '';
                        break;
                    }
                }
            }
        });
    }
});
</script>
