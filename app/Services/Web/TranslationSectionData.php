<?php

namespace App\Services\Web;

use App\Models\Context;
use App\Models\Language;
use Illuminate\Support\Collection;

class TranslationSectionData
{
    /**
     * @template TTranslation
     *
     * @param  Collection<int, TTranslation>  $translations
     * @return Collection<int, array{label: string|null, is_default: bool, translations: Collection<int, TTranslation>}>
     */
    public function build(Collection $translations, bool $groupByContext = true): Collection
    {
        if ($translations->isEmpty()) {
            /** @var Collection<int, array{label: string|null, is_default: bool, translations: Collection<int, TTranslation>}> $empty */
            $empty = collect([]);

            return $empty;
        }

        $defaultLanguageId = Language::query()
            ->where('is_default', true)
            ->value('id');

        $sortedTranslations = $translations
            ->sortBy(function ($translation) use ($defaultLanguageId): string {
                /** @var object{language_id: string|null, language: object{internal_name: string|null}|null} $translation */
                return sprintf(
                    '%d-%s',
                    $translation->language_id === $defaultLanguageId ? 0 : 1,
                    $translation->language->internal_name ?? $translation->language_id
                );
            })
            ->values();

        if (! $groupByContext) {
            /** @var string|null $label */
            $label = null;

            return collect([
                [
                    'label' => $label,
                    'is_default' => false,
                    'translations' => $sortedTranslations,
                ],
            ]);
        }

        $defaultContextId = Context::query()
            ->where('is_default', true)
            ->value('id');

        /** @var Collection<int, array{label: string|null, is_default: bool, translations: Collection<int, TTranslation>}> $result */
        $result = $sortedTranslations
            ->groupBy(function ($translation): string {
                /** @var object{context_id: string|null} $translation */
                return $translation->context_id ?? '__none__';
            })
            ->map(function (Collection $group, string $contextKey) use ($defaultContextId): array {
                /** @var object{context: Context|null}|null $firstItem */
                $firstItem = $group->first();
                $context = is_object($firstItem) ? $firstItem->context : null;
                $isDefaultContext = $context?->id === $defaultContextId;
                /** @var string|null $label */
                $label = $context !== null ? $context->internal_name : 'No Context';

                return [
                    'label' => $label,
                    'is_default' => $isDefaultContext,
                    'translations' => $group->values(),
                    'sort_key' => sprintf(
                        '%d-%s',
                        $isDefaultContext ? 0 : ($contextKey === '__none__' ? 2 : 1),
                        $context !== null ? $context->internal_name : 'zzz'
                    ),
                ];
            })
            ->sortBy('sort_key')
            ->values()
            ->map(function (array $group): array {
                /** @var array{label: string|null, is_default: bool, translations: Collection<int, TTranslation>, sort_key: string} $group */
                return [
                    'label' => $group['label'],
                    'is_default' => $group['is_default'],
                    'translations' => $group['translations'],
                ];
            });

        return $result;
    }
}
