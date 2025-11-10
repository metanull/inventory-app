{{--
    Sidebar Card for Tags
    Compact display with inline add/remove for two-column layout
--}}

@props(['model'])

@php($tc = $entityColor('tags'))

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
    <div class="flex items-start justify-between gap-2 mb-3">
        <h3 class="text-sm font-semibold text-gray-900">Tags</h3>
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <button 
                type="button"
                onclick="document.getElementById('add-tag-form-sidebar').classList.toggle('hidden')"
                class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                <x-heroicon-o-plus class="w-3 h-3" />
            </button>
        @endcan
    </div>

    @if($model->tags->isEmpty())
        <p class="text-xs text-gray-500 italic">No tags</p>
    @else
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach($model->tags as $tag)
                <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-gray-100 hover:bg-gray-200 transition-colors group">
                    <a href="{{ route('tags.show', $tag) }}" 
                       class="font-medium {{ $tc['text'] }} hover:underline">
                        {{ $tag->internal_name }}
                    </a>
                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                        <button 
                            type="button"
                            onclick="if(confirm('Remove tag?')) { document.getElementById('remove-tag-form-{{ $tag->id }}').submit(); }"
                            class="text-gray-400 hover:text-red-600 transition-colors ml-0.5">
                            <x-heroicon-o-x-mark class="w-3 h-3" />
                        </button>
                        <form id="remove-tag-form-{{ $tag->id }}" action="{{ route('items.tags.detach', [$model, $tag]) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                </div>
            @endforeach
        </div>
    @endif

    <!-- Add Tag Form (Hidden by default) -->
    @can(\App\Enums\Permission::UPDATE_DATA->value)
        <div id="add-tag-form-sidebar" class="hidden pt-3 border-t border-gray-100">
            <form action="{{ route('items.tags.attach', $model) }}" method="POST" class="space-y-2">
                @csrf
                <div>
                    <x-form.entity-select 
                        name="tag_id" 
                        :value="null"
                        :options="\App\Models\Tag::orderBy('internal_name')->get()->reject(fn($tag) => $model->tags->contains($tag->id))"
                        displayField="internal_name"
                        placeholder="Select a tag..."
                        searchPlaceholder="Type to search..."
                        required
                        entity="tags"
                    />
                </div>
                <div class="flex gap-2">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}">
                        Add
                    </button>
                    <button 
                        type="button"
                        onclick="document.getElementById('add-tag-form-sidebar').classList.add('hidden')"
                        class="inline-flex items-center px-2 py-1 rounded text-xs border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endcan
</div>
