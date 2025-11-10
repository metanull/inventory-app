{{--
    Sidebar Card for Links
    Groups links by context for better organization
--}}

@props(['model'])

@php
    $tc = $entityColor('item-item-links');
@endphp

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
    <div class="flex items-center justify-between gap-2 mb-3">
        <h3 class="text-sm font-semibold text-gray-900">Links</h3>
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <button 
                type="button"
                onclick="document.getElementById('add-link-form-sidebar').classList.toggle('hidden')"
                class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                <x-heroicon-o-plus class="w-3 h-3" />
            </button>
        @endcan
    </div>

    @php
        $allLinks = $model->outgoingLinks->concat($model->incomingLinks);
    @endphp
    
    @if($allLinks->isEmpty())
        <p class="text-xs text-gray-500 italic mb-3">No links</p>
    @else
        @php
            // Prepare links with direction and target item info
            $formattedLinks = collect();
            foreach ($model->outgoingLinks as $link) {
                $formattedLinks->push((object)[
                    'id' => $link->id,
                    'item' => $link->target,
                    'direction' => 'outgoing',
                    'link' => $link,
                ]);
            }
            foreach ($model->incomingLinks as $link) {
                $formattedLinks->push((object)[
                    'id' => $link->id,
                    'item' => $link->source,
                    'direction' => 'incoming',
                    'link' => $link,
                ]);
            }
        @endphp

        <div class="space-y-2 mb-3">
            @foreach($formattedLinks as $linkItem)
                <div class="flex items-start gap-2 p-2 rounded hover:bg-gray-50 transition-colors group">
                    <!-- Type Icon -->
                    <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center shrink-0">
                        <x-display.item-type-icon :type="$linkItem->item->type" class="w-4 h-4 text-gray-600" />
                    </div>
                    
                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            @if($linkItem->direction === 'outgoing')
                                <span class="text-blue-600 font-bold text-sm shrink-0" title="Outgoing link">»</span>
                            @else
                                <span class="text-green-600 font-bold text-sm shrink-0" title="Incoming link">«</span>
                            @endif
                            <a href="{{ route('items.show', $linkItem->item) }}" 
                               class="text-xs font-medium {{ $tc['accentLink'] }} hover:underline truncate block">
                                {{ $linkItem->item->internal_name }}
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 font-mono">
                            <x-format.uuid :uuid="$linkItem->item->id" format="long" />
                        </p>
                        @if($linkItem->link->context)
                            <p class="text-xs text-gray-400">{{ $linkItem->link->context->internal_name }}</p>
                        @endif
                    </div>

                    <!-- Remove Button -->
                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                        <button 
                            type="button"
                            onclick="if(confirm('Remove link?')) { document.getElementById('remove-link-form-{{ $linkItem->id }}').submit(); }"
                            class="text-gray-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                            <x-heroicon-o-x-mark class="w-3 h-3" />
                        </button>
                        <form id="remove-link-form-{{ $linkItem->id }}" action="{{ route('item-links.destroy', [$model, $linkItem->link]) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                </div>
            @endforeach
        </div>
    @endif

    <!-- Add Link Form (Hidden by default) -->
    @can(\App\Enums\Permission::UPDATE_DATA->value)
        @php
            $formClasses = 'hidden';
            if ($allLinks->isNotEmpty()) {
                $formClasses .= ' pt-3 border-t border-gray-100';
            }
        @endphp
        <div id="add-link-form-sidebar" class="{{ $formClasses }}">
            <form action="{{ route('item-links.store', ['item' => $model, 'return_to' => 'item']) }}" method="POST" class="space-y-2">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Target Item</label>
                    <x-form.entity-select 
                        name="target_id" 
                        :value="null"
                        :model-class="\App\Models\Item::class"
                        display-field="internal_name"
                        value-field="id"
                        placeholder="Search items to link..."
                        search-placeholder="Type name or ID..."
                        required
                        entity="items"
                        :filter-column="'id'"
                        :filter-operator="'!='"
                        :filter-value="$model->id"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Context</label>
                    <x-form.entity-select 
                        name="context_id" 
                        :value="null"
                        :options="\App\Models\Context::orderBy('internal_name')->get()"
                        display-field="internal_name"
                        placeholder="Select context..."
                        search-placeholder="Type to search..."
                        required
                        entity="contexts"
                    />
                </div>
                <div class="flex gap-2">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                        Add Link
                    </button>
                    <button 
                        type="button"
                        onclick="document.getElementById('add-link-form-sidebar').classList.add('hidden')"
                        class="inline-flex items-center px-2 py-1 rounded text-xs border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endcan
</div>

