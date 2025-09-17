@props([
    'countries' => collect(), // collection of Country (id, internal_name)
    'value' => null,
    'name' => 'country_id',
    'label' => 'Country',
    'placeholder' => 'Select a country...',
])

@php($c = $entityColor('partners'))

<label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
<div
    x-data="countrySelectComponent({
        value: @js($value),
        options: @js($countries->map(fn($c)=>['id'=>$c->id,'label'=>$c->internal_name])->toArray()),
        name: @js($name)
    })"
    x-init="init()"
    class="mt-1 relative"
>
    @if(($countries instanceof \Illuminate\Support\Collection ? $countries->isEmpty() : empty($countries)))
        <x-ui.alert type="warning" class="mb-2">
            No countries available. Please seed countries or contact an administrator.
        </x-ui.alert>
    @endif
    <input type="hidden" :name="name" x-model="selectedValue" />
    <button type="button" @click="if(options.length){ open = !open }" :disabled="!options.length" :class="['w-full text-left rounded-md border-gray-300 {{$c['focus']}} px-3 py-2 bg-white flex justify-between items-center', options.length ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed', open ? 'ring-2 ring-offset-1 {{$c['ring'] ?? 'ring-indigo-500'}}' : '']">
        <span x-text="selectedLabel || (options.length ? '{{ $placeholder }}' : 'No data')" :class="{'text-gray-400': !selectedLabel}"></span>
        <x-heroicon-o-chevron-up-down class="w-5 h-5 text-gray-400" />
    </button>
    <template x-if="open">
        <div @click.outside="open=false" class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto">
            <div class="p-2 border-b border-gray-100">
                <input type="text" x-model="search" placeholder="Search..." class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
            </div>
            <ul class="py-1 text-sm">
                <template x-for="opt in filtered" :key="opt.id">
                    <li>
                        <button type="button" @click="choose(opt)" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex justify-between items-center" :class="{'bg-indigo-50': opt.id===selectedValue}">
                            <span>
                                <span x-text="opt.label"></span>
                                <span class="text-xs text-gray-400 ml-1" x-text="'(' + opt.id + ')'" />
                            </span>
                            <x-heroicon-o-check class="w-4 h-4 text-indigo-600" x-show="opt.id===selectedValue" />
                        </button>
                    </li>
                </template>
                <template x-if="filtered.length===0">
                    <li class="px-3 py-2 text-xs text-gray-500">No results</li>
                </template>
            </ul>
            <div class="p-2 border-t border-gray-100 flex justify-between">
                <button type="button" @click="clear()" class="text-xs text-gray-500 hover:text-gray-700">Clear</button>
                <button type="button" @click="open=false" class="text-xs text-indigo-600 hover:text-indigo-700">Close</button>
            </div>
        </div>
    </template>
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<script>
    function countrySelectComponent({ value, options, name }) {
        return {
            name,
            open: false,
            search: '',
            options,
            selectedValue: value || null,
            get selectedLabel() {
                const m = this.options.find(o => o.id === this.selectedValue);
                return m ? m.label : '';
            },
            get filtered() {
                const term = this.search.trim().toLowerCase();
                if (!term) return this.options;
                return this.options.filter(o => o.label.toLowerCase().includes(term) || o.id.toLowerCase().includes(term));
            },
            init() {
                // Normalize to uppercase when the hidden field updates
                this.$watch('selectedValue', (val) => {
                    if (val) this.selectedValue = val.toUpperCase();
                });
            },
            choose(opt) {
                this.selectedValue = opt.id.toUpperCase();
                this.open = false;
            },
            clear() {
                this.selectedValue = null;
                this.search = '';
            }
        }
    }
</script>