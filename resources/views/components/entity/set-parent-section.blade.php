@props(['model'])

@php($tc = $entityColor('items'))
<div class="mt-8">
    <x-layout.section title="Parent Item" icon="arrow-up-circle">
        <div class="bg-gray-50 rounded-md p-4">
            <p class="text-sm text-gray-600 mb-4">This item has no parent. Set a parent to establish hierarchical relationship.</p>
            
            <form action="{{ route('items.setParent', $model) }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Select Parent Item *</label>
                    <select 
                        name="parent_id" 
                        id="parent_id" 
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Select a parent item --</option>
                        @foreach(\App\Models\Item::where('id', '!=', $model->id)->orderBy('internal_name')->get() as $availableItem)
                            <option value="{{ $availableItem->id }}">
                                {{ $availableItem->internal_name }}
                                @if($availableItem->backward_compatibility)
                                    ({{ $availableItem->backward_compatibility }})
                                @endif
                                - {{ ucfirst($availableItem->type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="px-4 py-2 {{ $tc['button'] }} text-white rounded-md text-sm font-medium">
                        Set Parent
                    </button>
                </div>
            </form>
        </div>
    </x-layout.section>
</div>
