@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="countries" title="Countries">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('countries.create')" variant="primary" entity="countries" icon="plus">
                Add Country
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="countries" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search internal name..." />

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="id" label="Code" class="hidden sm:table-cell" />
                    <x-table.sortable-header field="internal_name" label="Internal Name" />
                    <x-table.header-cell hidden="hidden md:table-cell">Legacy</x-table.header-cell>
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($countries as $country)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('countries.show', $country) }}'">
                            <td class="hidden sm:table-cell px-4 py-3 text-sm font-mono">{{ $country->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $country->internal_name }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-500">{{ $country->backward_compatibility ?? '—' }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                                <x-table.row-actions
                                    :view="route('countries.show', $country)"
                                    :edit="route('countries.edit', $country)"
                                    :delete="route('countries.destroy', $country)"
                                    delete-confirm="Delete this country?"
                                    entity="countries"
                                    :record-id="$country->id"
                                    :record-name="$country->internal_name"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No countries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$countries"
                entity="countries"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
