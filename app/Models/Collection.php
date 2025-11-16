<?php

namespace App\Models;

use App\Enums\PartnerLevel;
use Illuminate\Database\Eloquent\Builder;
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

    // Type constants
    public const TYPE_COLLECTION = 'collection';

    public const TYPE_EXHIBITION = 'exhibition';

    public const TYPE_GALLERY = 'gallery';

    public const TYPE_THEME = 'theme';

    public const TYPE_EXHIBITION_TRAIL = 'exhibition trail';

    public const TYPE_ITINERARY = 'itinerary';

    public const TYPE_LOCATION = 'location';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_name',
        'type',
        'language_id',
        'context_id',
        'parent_id',
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
     * Get the parent collection (for hierarchical organization).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'parent_id');
    }

    /**
     * Get all child collections.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Collection::class, 'parent_id');
    }

    /**
     * Get all items belonging to this collection (primary relationship).
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get all items attached to this collection via many-to-many relationship.
     */
    public function attachedItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'collection_item')
            ->withTimestamps();
    }

    /**
     * Get all images belonging to this collection.
     */
    public function collectionImages(): HasMany
    {
        return $this->hasMany(CollectionImage::class);
    }

    /**
     * Scope to get only collection type collections.
     */
    public function scopeCollections(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_COLLECTION);
    }

    /**
     * Scope to get only exhibition type collections.
     */
    public function scopeExhibitions(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EXHIBITION);
    }

    /**
     * Scope to get only gallery type collections.
     */
    public function scopeGalleries(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_GALLERY);
    }

    /**
     * Scope to get only theme type collections.
     */
    public function scopeThemes(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_THEME);
    }

    /**
     * Scope to get only exhibition trail type collections.
     */
    public function scopeExhibitionTrails(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EXHIBITION_TRAIL);
    }

    /**
     * Scope to get only itinerary type collections.
     */
    public function scopeItineraries(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ITINERARY);
    }

    /**
     * Scope to get only location type collections.
     */
    public function scopeLocations(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_LOCATION);
    }

    /**
     * Scope to get only root collections (no parent).
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get child collections of a specific parent.
     */
    public function scopeChildrenOf(Builder $query, string $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
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

    /**
     * Attach an item to this collection via many-to-many relationship.
     */
    public function attachItem(Item $item): void
    {
        $this->attachedItems()->syncWithoutDetaching([$item->id]);
    }

    /**
     * Detach an item from this collection.
     */
    public function detachItem(Item $item): void
    {
        $this->attachedItems()->detach($item->id);
    }

    /**
     * Attach multiple items to this collection.
     */
    public function attachItems(array $itemIds): void
    {
        $this->attachedItems()->syncWithoutDetaching($itemIds);
    }

    /**
     * Detach multiple items from this collection.
     */
    public function detachItems(array $itemIds): void
    {
        $this->attachedItems()->detach($itemIds);
    }
}
