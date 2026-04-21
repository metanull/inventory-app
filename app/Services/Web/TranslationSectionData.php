<?php

namespace App\Services\Web;

use App\Models\Context;
use App\Models\Language;
use Illuminate\Support\Collection;

class TranslationSectionData
{
    /**
     * @param  Collection<int, mixed>  $translations
     * @return Collection<int, array{label: string|null, is_default: bool, translations: Collection<int, mixed>}>
     */
    public function build(Collection $translations, bool $groupByContext = true): Collection
    {
        if ($translations->isEmpty()) {
            return collect();
        }

        $defaultLanguageId = Language::query()
            ->where('is_default', true)
            ->value('id');

        $sortedTranslations = $translations
            ->sortBy(fn ($translation): string => sprintf(
                '%d-%s',
                $translation->language_id === $defaultLanguageId ? 0 : 1,
                $translation->language?->internal_name ?? $translation->language_id
            ))
            ->values();

        if (! $groupByContext) {
            return collect([
                [
                    'label' => null,
                    'is_default' => false,
                    'translations' => $sortedTranslations,
                ],
            ]);
        }

        $defaultContextId = Context::query()
            ->where('is_default', true)
            ->value('id');

        return $sortedTranslations
            ->groupBy(fn ($translation): string => $translation->context_id ?? '__none__')
            ->map(function (Collection $group, string $contextKey) use ($defaultContextId): array {
                $context = $group->first()?->context;
                $isDefaultContext = $context?->id === $defaultContextId;

                return [
                    'label' => $context?->internal_name ?? 'No Context',
                    'is_default' => $isDefaultContext,
                    'translations' => $group->values(),
                    'sort_key' => sprintf(
                        '%d-%s',
                        $isDefaultContext ? 0 : ($contextKey === '__none__' ? 2 : 1),
                        $context?->internal_name ?? 'zzz'
                    ),
                ];
            })
            ->sortBy('sort_key')
            ->values()
            ->map(function (array $group): array {
                unset($group['sort_key']);

                return $group;
            });
    }
}
