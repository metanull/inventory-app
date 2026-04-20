@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="contexts" title="Contexts">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('contexts.create')" variant="primary" entity="contexts" icon="plus">
                Add Context
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="contexts" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search internal name..." />

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="internal_name" label="Internal Name" />
                    <x-table.sortable-header field="is_default" label="Default" class="hidden md:table-cell" />
                    <x-table.sortable-header field="created_at" label="Created" class="hidden lg:table-cell" />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($contexts as $context)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('contexts.show', $context) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $context->internal_name }}</td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm">
                                <x-ui.badge :color="$context->is_default ? 'green' : 'gray'" variant="pill">
                                    {{ $context->is_default ? 'Yes' : 'No' }}
                                </x-ui.badge>
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($context->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                                <x-table.row-actions
                                    :view="route('contexts.show', $context)"
                                    :edit="route('contexts.edit', $context)"
                                    :delete="route('contexts.destroy', $context)"
                                    delete-confirm="Delete this context?"
                                    entity="contexts"
                                    :record-id="$context->id"
                                    :record-name="$context->internal_name"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No contexts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$contexts"
                entity="contexts"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
