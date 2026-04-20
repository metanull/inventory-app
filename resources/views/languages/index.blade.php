@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="languages" title="Languages">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('languages.create')" variant="primary" entity="languages" icon="plus">
                Add Language
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="languages" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search internal name..." />

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="id" label="Code" class="hidden sm:table-cell" />
                    <x-table.sortable-header field="internal_name" label="Internal Name" />
                    <x-table.header-cell hidden="hidden lg:table-cell">Legacy</x-table.header-cell>
                    <x-table.header-cell hidden="hidden md:table-cell">Default</x-table.header-cell>
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($languages as $language)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('languages.show', $language) }}'">
                            <td class="hidden sm:table-cell px-4 py-3 text-sm font-mono">{{ $language->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $language->internal_name }}</td>
                            <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $language->backward_compatibility ?? '—' }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm">{!! $language->is_default ? '<span class="text-green-600 font-medium">Yes</span>' : 'No' !!}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                                <x-table.row-actions
                                    :view="route('languages.show', $language)"
                                    :edit="route('languages.edit', $language)"
                                    :delete="route('languages.destroy', $language)"
                                    delete-confirm="Delete this language?"
                                    entity="languages"
                                    :record-id="$language->id"
                                    :record-name="$language->internal_name"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No languages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$languages"
                entity="languages"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
