<?php

namespace App\Models;

use App\Enums\PartnerLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Collection Model
 *
 * Represents a collection of museum items with translation and partner support.
 * Collections organize items and provide context for display purposes.
 */
class Collection extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_name',
        'language_id',
        'context_id',
        'backward_compatibility',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get all translations for this collection.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CollectionTranslation::class);
    }

    /**
     * Get the default language for this collection.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the default context for this collection.
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * Get all items belonging to this collection.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get all partners associated with this collection.
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'collection_partner', 'collection_id', 'partner_id')
            ->wherePivot('collection_type', '=', 'collection')
            ->withPivot(['collection_type', 'level'])
            ->withTimestamps()
            ->using(CollectionPartner::class);
    }

    /**
     * Get the default context translation for this collection in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?CollectionTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this collection.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?CollectionTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?CollectionTranslation
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
     * Get partners by level.
     */
    public function partnersByLevel(PartnerLevel $level): BelongsToMany
    {
        return $this->partners()->wherePivot('level', $level->value);
    }

    /**
     * Get direct partners (level: partner).
     */
    public function directPartners(): BelongsToMany
    {
        return $this->partnersByLevel(PartnerLevel::PARTNER);
    }

    /**
     * Get associated partners (level: associated_partner).
     */
    public function associatedPartners(): BelongsToMany
    {
        return $this->partnersByLevel(PartnerLevel::ASSOCIATED_PARTNER);
    }

    /**
     * Get minor contributors (level: minor_contributor).
     */
    public function minorContributors(): BelongsToMany
    {
        return $this->partnersByLevel(PartnerLevel::MINOR_CONTRIBUTOR);
    }
}
