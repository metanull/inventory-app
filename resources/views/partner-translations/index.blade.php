@extends('layouts.app')

@section('content')
    <x-layout.index-page entity="partner-translations">
        @php
            $currentQuery = $listState->query();
            $formQuery = $listState->query(['q', 'page']);
            $sortQuery = $listState->query(['sort', 'direction', 'page']);
        @endphp

        <x-list.parent-context-header
            :breadcrumbs="[
                ['label' => 'Partners', 'url' => route('partners.index')],
                ['label' => $partner->internal_name, 'url' => route('partners.show', $partner)],
                ['label' => 'Translations'],
            ]"
            title="Translations"
            parent-label="Partner"
            :parent-value="$partner->internal_name"
            :parent-url="route('partners.show', $partner)"
            :back-url="route('partners.show', $partner)"
            back-label="Back to Partner"
        />

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <x-list.search-form
                    :action="route('partner-translations.index')"
                    :query="$formQuery"
                    :search="$listState->search"
                    placeholder="Search translation names..."
                    :clear-url="route('partner-translations.index', ['partner_id' => $listState->filters['partner_id']])"
                >
                    <input type="hidden" name="partner_id" value="{{ $listState->filters['partner_id'] }}">

                    <div class="w-full md:w-52">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Language</label>
                        <x-form.entity-select
                            name="language"
                            :modelClass="\App\Models\Language::class"
                            displayField="internal_name"
                            :value="$selectedLanguage?->id"
                            entity="languages"
                            placeholder="All languages"
                        />
                    </div>

                    <div class="w-full md:w-52">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Context</label>
                        <x-form.entity-select
                            name="context"
                            :modelClass="\App\Models\Context::class"
                            displayField="internal_name"
                            :value="$selectedContext?->id"
                            entity="contexts"
                            placeholder="All contexts"
                        />
                    </div>
                </x-list.search-form>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <x-table.header>
                        <x-list.sort-link
                            label="Name"
                            field="language.internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partner-translations.index')"
                            :query="$sortQuery"
                        />
                        <x-list.sort-link
                            label="Language"
                            field="language.internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partner-translations.index')"
                            :query="$sortQuery"
                            class="hidden md:table-cell"
                        />
                        <x-list.sort-link
                            label="Context"
                            field="context.internal_name"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partner-translations.index')"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-list.sort-link
                            label="Updated"
                            field="updated_at"
                            :current-sort="$listState->sort"
                            :current-direction="$listState->direction"
                            :url="route('partner-translations.index')"
                            :query="$sortQuery"
                            class="hidden lg:table-cell"
                        />
                        <x-table.header-cell hidden="hidden sm:table-cell">
                            <span class="sr-only">Actions</span>
                        </x-table.header-cell>
                    </x-table.header>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($partnerTranslations as $translation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('partner-translations.show', $translation) }}" class="text-yellow-700 hover:text-yellow-900 hover:underline">
                                        {{ $translation->name ?? '—' }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600">{{ $translation->language?->internal_name ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-sm text-gray-500">{{ $translation->context?->internal_name ?? '—' }}</td>
                                <td class="hidden lg:table-cell px-4 py-3 text-xs text-gray-400">{{ optional($translation->updated_at)->format('Y-m-d H:i') }}</td>
                                <td class="hidden sm:table-cell px-4 py-3 text-right text-sm">
                                    <x-table.row-actions
                                        :view="route('partner-translations.show', $translation)"
                                        :edit="route('partner-translations.edit', $translation)"
                                        :delete="route('partner-translations.destroy', $translation)"
                                        delete-confirm="Delete this partner translation?"
                                        entity="partner-translations"
                                        :record-id="$translation->id"
                                        :record-name="$translation->name"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No translations found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-list.pagination
                :paginator="$partnerTranslations"
                :action="route('partner-translations.index')"
                :query="$currentQuery"
                :current-per-page="$listState->perPage"
                entity="partner-translations"
            />
        </div>
    </x-layout.index-page>
@endsection
