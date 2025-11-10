@props(['model'])

@php($tc = $entityColor('collections'))
<div class="mt-8">
    <x-layout.section title="Items in Collection" icon="cube">
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <x-slot name="action">
                <button 
                    type="button"
                    onclick="document.getElementById('attachItemModal').classList.remove('hidden')"
                    class="inline-flex items-center px-3 py-2 rounded-md {{ $tc['button'] }} text-sm font-medium">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Add Item
                </button>
            </x-slot>
        @endcan

        @if($model->attachedItems->isEmpty())
            <p class="text-sm text-gray-500 italic">No items in this collection</p>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($model->attachedItems as $item)
                        <li class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-start space-x-4">
                                <!-- Thumbnail -->
                                                        <!-- Thumbnail -->
                        <div class="shrink-0 w-12 h-12">
                            @if($image = $item->itemImages->first())
                                        <img src="{{ Storage::url($image->image_path) }}" 
                                             alt="{{ $item->internal_name }}"
                                             class="w-12 h-12 rounded object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center">
                                            <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('items.show', $item) }}" 
                                           class="text-blue-600 hover:text-blue-900 font-medium">
                                            {{ $item->internal_name }}
                                        </a>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <x-display.item-type-icon :type="$item->type" class="w-3 h-3 mr-1" />
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </div>
                                    @if($item->backward_compatibility)
                                        <p class="text-xs text-gray-500 mt-1">Legacy ID: {{ $item->backward_compatibility }}</p>
                                    @endif
                                </div>
                                
                                <!-- Actions -->
                                <div class="shrink-0">
                                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                                        <form action="{{ route('collections.detachItem', [$model, $item]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit"
                                                onclick="return confirm('Remove this item from the collection?')"
                                                class="text-red-600 hover:text-red-900">
                                                <x-heroicon-o-x-mark class="w-5 h-5" />
                                                <span class="sr-only">Remove</span>
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-layout.section>
</div>

<!-- Attach Item Modal -->
@can(\App\Enums\Permission::UPDATE_DATA->value)
<div id="attachItemModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Add Item to Collection</h3>
                <button 
                    type="button"
                    onclick="document.getElementById('attachItemModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-500">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
        </div>
        <form action="{{ route('collections.attachItem', $model) }}" method="POST" class="px-6 py-4">
            @csrf
            <div class="space-y-4">
                <x-form.field label="Select Item" name="item_id" required>
                    <x-form.entity-select 
                        name="item_id"
                        :modelClass="\App\Models\Item::class"
                        displayField="internal_name"
                        placeholder="Select an item..."
                        searchPlaceholder="Type to search items..."
                        entity="items"
                        :required="true"
                        filterColumn="id"
                        filterOperator="NOT IN"
                        :filterValue="$model->attachedItems->pluck('id')->toArray()"
                    />
                </x-form.field>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button 
                    type="button"
                    onclick="document.getElementById('attachItemModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white {{ $tc['button'] }} rounded-md">
                    Add Item
                </button>
            </div>
        </form>
    </div>
</div>
@endcan
