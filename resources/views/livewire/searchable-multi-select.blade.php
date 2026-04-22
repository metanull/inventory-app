<div class="relative">
    {{-- Hidden inputs for form submission: emitted as name[] per selected id --}}
    @foreach($selectedIds as $id)
        <input type="hidden" name="{{ $name }}[]" value="{{ $id }}" />
    @endforeach

    {{-- Chips for currently selected options --}}
    @if($selectedOptions->isNotEmpty())
        <div class="flex flex-wrap gap-1 mb-2">
            @foreach($selectedOptions as $option)
                @php
                    $chipId = (string) (is_object($option) ? ($option->id ?? null) : ($option['id'] ?? null));
                    $chipLabel = is_object($option) ? ($option->{$displayField} ?? '') : ($option[$displayField] ?? '');
                @endphp
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                    {{ $chipLabel }}
                    <button
                        type="button"
                        wire:click="removeOption('{{ $chipId }}')"
                        class="text-gray-400 hover:text-gray-600"
                        aria-label="Remove {{ $chipLabel }}"
                    >
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>
            @endforeach

            <button
                type="button"
                wire:click="clear"
                class="text-xs text-gray-400 hover:text-gray-600 self-center ml-1"
            >
                Clear all
            </button>
        </div>
    @endif

    {{-- Search / combobox input --}}
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            @focus="$wire.set('open', true)"
            @click.away="$wire.set('open', false); $wire.set('search', '')"
            @keydown.escape="$wire.set('open', false); $wire.set('search', '')"
            placeholder="{{ $searchPlaceholder }}"
            class="block w-full rounded-md border-gray-300 shadow-sm {{ $focusClasses }} sm:text-sm"
            autocomplete="off"
        />

        {{-- Dropdown chevron --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>

    {{-- Candidate options dropdown --}}
    @if($open && $options->isNotEmpty())
        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
            @foreach($options as $option)
                @php
                    $optionValue = (string) (is_object($option) ? ($option->id ?? null) : ($option['id'] ?? null));
                    $optionDisplay = is_object($option) ? ($option->{$displayField} ?? '') : ($option[$displayField] ?? '');
                @endphp
                <div
                    wire:click="addOption('{{ $optionValue }}')"
                    class="cursor-pointer select-none relative py-2 px-3 hover:bg-gray-50"
                >
                    <span class="block truncate font-medium">{{ $optionDisplay }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- No results message --}}
    @if($open && $search !== '' && $options->isEmpty())
        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-3 px-3 text-sm text-gray-500 ring-1 ring-black ring-opacity-5">
            No results found for "{{ $search }}"
        </div>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
