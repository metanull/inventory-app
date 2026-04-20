@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="item-item-links" title="Links for {{ $item->internal_name }}">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('item-links.create', $item)" variant="primary" entity="item-item-links" icon="plus">
                Add Link
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="item-item-links" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search by target item...">
            @if($contexts->count() > 0)
                <div class="flex items-center gap-2">
                    <label for="contextFilter" class="text-sm text-gray-700">Context:</label>
                    <select name="context" id="contextFilter"
                            class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            onchange="this.form.submit()">
                        <option value="" @selected($contextFilter === '')>All Contexts</option>
                        @foreach($contexts as $context)
                            <option value="{{ $context->id }}" @selected($contextFilter === $context->id)>{{ $context->internal_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </x-table.filter-bar>

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.header-cell>Target Item</x-table.header-cell>
                    <x-table.header-cell hidden="hidden md:table-cell">Context</x-table.header-cell>
                    <x-table.sortable-header field="created_at" label="Created" class="hidden lg:table-cell" />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($links as $link)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('item-links.show', [$link->source, $link]) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $link->target->internal_name }}
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-700">
                                {{ $link->context->internal_name }}
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($link->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                                <x-table.row-actions
                                    :view="route('item-links.show', [$link->source, $link])"
                                    :edit="route('item-links.edit', [$link->source, $link])"
                                    :delete="route('item-links.destroy', [$link->source, $link])"
                                    delete-confirm="Delete this link?"
                                    entity="item-item-links"
                                    :record-id="$link->id"
                                    :record-name="'Link to ' . $link->target->internal_name"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No links found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$links"
                entity="item-item-links"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection

