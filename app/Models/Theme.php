<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Theme Model
 *
 * Represents a Theme or Subtheme within an Exhibition.
 * Supports translation and parent-child hierarchy (2 levels: theme, subtheme).
 */
class Theme extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    protected $fillable = [
        'collection_id',
        'parent_id',
        'display_order',
        'internal_name',
        'backward_compatibility',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'parent_id');
    }

    public function subthemes(): HasMany
    {
        return $this->hasMany(Theme::class, 'parent_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ThemeTranslation::class);
    }

    /**
     * Get the default context translation for this theme in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?ThemeTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this theme.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?ThemeTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?ThemeTranslation
    {
        if ($contextId) {
            $translation = $this->getContextualizedTranslation($languageId, $contextId);
            if ($translation) {
                return $translation;
            }
        }

        return $this->getDefaultTranslation($languageId);
    }

    /**
     * Get a query builder scoped to this theme's siblings.
     * - For root themes (parent_id is null): siblings share the same collection_id
     * - For subthemes: siblings share the same parent_id
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        if ($this->parent_id !== null) {
            // Subtheme: siblings are other subthemes of the same parent
            return static::where('parent_id', $this->parent_id);
        }

        // Root theme: siblings are other root themes in the same collection
        return static::where('collection_id', $this->collection_id)
            ->whereNull('parent_id');
    }

    public function uniqueIds(): array
    {
        return ['id'];
    }
}
