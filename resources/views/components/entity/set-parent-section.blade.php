@props(['model'])

@php($tc = $entityColor('items'))
<div class="mt-8">
    <x-layout.section title="Parent Item" icon="arrow-up-circle">
        <div class="bg-gray-50 rounded-md p-4">
            <p class="text-sm text-gray-600 mb-4">This item has no parent. Set a parent to establish hierarchical relationship.</p>
            
            <form action="{{ route('items.setParent', $model) }}" method="POST" class="space-y-4">
                @csrf
                
                <x-form.field label="Select Parent Item" name="parent_id" required>
                    <x-form.entity-select 
                        name="parent_id"
                        :modelClass="\App\Models\Item::class"
                        displayField="internal_name"
                        placeholder="Select a parent item..."
                        searchPlaceholder="Type to search items..."
                        entity="items"
                        :required="true"
                        filterColumn="id"
                        filterOperator="!="
                        :filterValue="$model->id"
                    />
                </x-form.field>
                
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
