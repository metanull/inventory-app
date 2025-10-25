@php($tc = $entityColor('tags'))
<div class="mt-8">
    <x-layout.section title="Tags" icon="tag">
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <x-slot:action>
                <x-ui.button 
                    type="button"
                    onclick="document.getElementById('add-tag-form').classList.toggle('hidden')"
                    variant="primary"
                    entity="tags"
                    icon="plus">
                    Add Tag
                </x-ui.button>
            </x-slot:action>
        @endcan

        <!-- Add Tag Form (Hidden by default) -->
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <div id="add-tag-form" class="hidden mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <form action="{{ route('items.tags.attach', $item) }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label for="tag_id" class="block text-sm font-medium text-gray-700 mb-1">Select Tag</label>
                        <x-form.entity-select 
                            name="tag_id" 
                            :value="null"
                            :options="\App\Models\Tag::orderBy('internal_name')->get()->reject(fn($tag) => $item->tags->contains($tag->id))"
                            displayField="internal_name"
                            placeholder="Select a tag..."
                            searchPlaceholder="Type to search tags..."
                            required
                            entity="tags"
                        />
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button 
                            type="submit"
                            variant="primary"
                            entity="tags">
                            Add
                        </x-ui.button>
                        <x-ui.button 
                            type="button"
                            onclick="document.getElementById('add-tag-form').classList.add('hidden')"
                            variant="secondary">
                            Cancel
                        </x-ui.button>
                    </div>
                </form>
            </div>
        @endcan

        <!-- Tags List -->
        @if($item->tags->isEmpty())
            <p class="text-sm text-gray-500 italic">No tags assigned</p>
        @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($item->tags as $tag)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('tags.show', $tag) }}" class="block hover:bg-gray-50 -mx-6 -my-4 px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium {{ $tc['text'] }}">{{ $tag->internal_name }}</p>
                                            @if($tag->description)
                                                <p class="text-sm text-gray-500 mt-1">{{ $tag->description }}</p>
                                            @endif
                                        </div>
                                        <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400" />
                                    </div>
                                </a>
                            </div>
                            @can(\App\Enums\Permission::UPDATE_DATA->value)
                                <form action="{{ route('items.tags.detach', [$item, $tag]) }}" method="POST" class="ml-4">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit"
                                        onclick="return confirm('Are you sure you want to remove this tag?')"
                                        class="text-red-600 hover:text-red-900"
                                        title="Remove tag"
                                    >
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    </x-layout.section>
</div>
