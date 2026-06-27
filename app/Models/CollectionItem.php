<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * CollectionItem Pivot Model
 *
 * Typed representation of a row in the collection_item table.
 * Exposes display order, contextual descriptions, and source
 * backward-compatibility values through named helpers.
 */
class CollectionItem extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collection_item';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'collection_id',
        'item_id',
        'display_order',
        'extra',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'display_order' => 'integer',
        'extra' => 'array',
    ];

    /**
     * Get the collection that owns the pivot.
     *
     * @return BelongsTo<Collection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the item that owns the pivot.
     *
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Return all language-keyed contextual descriptions.
     *
     * @return array<string, string>
     */
    public function contextualDescriptions(): array
    {
        $extra = is_array($this->extra) ? $this->extra : [];

        /** @var array<string, string> */
        return (array) ($extra['contextual_descriptions'] ?? []);
    }

    /**
     * Return the contextual description for a specific language, or null.
     */
    public function contextualDescriptionForLanguage(string $languageId): ?string
    {
        $descriptions = $this->contextualDescriptions();

        return isset($descriptions[$languageId]) && $descriptions[$languageId] !== ''
            ? (string) $descriptions[$languageId]
            : null;
    }

    /**
     * Return the language keys that have a contextual description.
     *
     * @return array<int, string>
     */
    public function contextualDescriptionLanguages(): array
    {
        return array_keys($this->contextualDescriptions());
    }

    /**
     * Return all language-keyed source backward-compatibility values.
     *
     * @return array<string, string>
     */
    public function sourceBackwardCompatibilityByLanguage(): array
    {
        $extra = is_array($this->extra) ? $this->extra : [];

        /** @var array<string, string> */
        return (array) ($extra['source_bc_by_language'] ?? []);
    }

    /**
     * Return the source backward-compatibility value for a specific language, or null.
     */
    public function sourceBackwardCompatibilityForLanguage(string $languageId): ?string
    {
        $sources = $this->sourceBackwardCompatibilityByLanguage();

        return isset($sources[$languageId]) && $sources[$languageId] !== ''
            ? (string) $sources[$languageId]
            : null;
    }
}
