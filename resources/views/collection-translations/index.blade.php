@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header entity="collection-translations" title="Collection Translations">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button :href="route('collection-translations.create')" variant="primary" entity="collection-translations" icon="plus">
                Add Translation
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" entity="collection-translations" />
    @endif

    <div class="space-y-4">
        <x-table.filter-bar name="q" placeholder="Search translations...">
            <div class="flex items-center gap-2">
                <label for="contextFilter" class="text-sm text-gray-700">Context:</label>
                <select name="context" id="contextFilter"
                        class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        onchange="this.form.submit()">
                    <option value="" @selected($contextFilter === '')>All Contexts</option>
                    <option value="default" @selected($contextFilter === 'default')>Default Context Only</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}" @selected($contextFilter === $context->id)>{{ $context->internal_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label for="languageFilter" class="text-sm text-gray-700">Language:</label>
                <select name="language" id="languageFilter"
                        class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        onchange="this.form.submit()">
                    <option value="" @selected($languageFilter === '')>All Languages</option>
                    <option value="default" @selected($languageFilter === 'default')>Default Language Only</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}" @selected($languageFilter === $language->id)>{{ $language->internal_name }}</option>
                    @endforeach
                </select>
            </div>
        </x-table.filter-bar>

        <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <x-table.header>
                    <x-table.sortable-header field="title" label="Title" />
                    <x-table.header-cell hidden="hidden md:table-cell">Collection</x-table.header-cell>
                    <x-table.header-cell hidden="hidden lg:table-cell">Language</x-table.header-cell>
                    <x-table.header-cell hidden="hidden lg:table-cell">Context</x-table.header-cell>
                    <x-table.sortable-header field="created_at" label="Created" class="hidden xl:table-cell" />
                    <x-table.header-cell hidden="hidden sm:table-cell">
                        <span class="sr-only">Actions</span>
                    </x-table.header-cell>
                </x-table.header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($collectionTranslations as $translation)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('collection-translations.show', $translation) }}'">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $translation->title }}
                                @if($translation->description)
                                    <div class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ Str::limit($translation->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-700">
                                {{ $translation->collection?->internal_name ?? 'N/A' }}
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-700">
                                {{ $translation->language?->internal_name ?? $translation->language_id }}
                            </td>
                            <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-700">
                                {{ $translation->context?->internal_name ?? 'N/A' }}
                                @if($translation->context?->is_default)
                                    <x-ui.badge color="green" variant="pill" size="sm">default</x-ui.badge>
                                @endif
                            </td>
                            <td class="hidden xl:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($translation->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="hidden sm:table-cell px-4 py-3 text-right text-sm" onclick="event.stopPropagation()">
                                <x-table.row-actions
                                    :view="route('collection-translations.show', $translation)"
                                    :edit="route('collection-translations.edit', $translation)"
                                    :delete="route('collection-translations.destroy', $translation)"
                                    delete-confirm="Delete this translation?"
                                    entity="collection-translations"
                                    :record-id="$translation->id"
                                    :record-name="$translation->title"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No translations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <x-layout.pagination
                :paginator="$collectionTranslations"
                entity="collection-translations"
                param-page="page"
            />
        </div>

        <x-table.delete-modal />
    </div>
</div>
@endsection
