<?php

namespace App\Models;

use App\Enums\PartnerLevel;
use App\Traits\HasDisplayOrder;
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
    use HasDisplayOrder, HasFactory, HasUuids;

    // Type constants
    public const TYPE_COLLECTION = 'collection';

    public const TYPE_EXHIBITION = 'exhibition';

    public const TYPE_GALLERY = 'gallery';

    public const TYPE_THEME = 'theme';

    public const TYPE_EXHIBITION_TRAIL = 'exhibition trail';

    public const TYPE_ITINERARY = 'itinerary';

    public const TYPE_LOCATION = 'location';

    public const TYPE_SUBTHEME = 'subtheme';

    public const TYPE_REGION = 'region';

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
        'display_order',
        'backward_compatibility',
        // GPS Location
        'latitude',
        'longitude',
        'map_zoom',
        // Country reference
        'country_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'display_order' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'map_zoom' => 'integer',
    ];

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
     * Get a query builder scoped to this collection's siblings (same parent_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return $this->parent_id
            ? static::where('parent_id', $this->parent_id)
            : static::whereNull('parent_id');
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
     * Get the country associated with this collection.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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
            ->withPivot('display_order', 'extra')
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
     * Get all media (audio/video URLs) belonging to this collection.
     */
    public function collectionMedia(): HasMany
    {
        return $this->hasMany(CollectionMedia::class)->orderBy('type')->orderBy('display_order');
    }

    /**
     * Get all contributors belonging to this collection.
     */
    public function contributors(): HasMany
    {
        return $this->hasMany(Contributor::class)->orderBy('display_order');
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

    /**
     * Scope to exclude collections with the given IDs.
     *
     * @param  array<int, string>  $ids
     */
    public function scopeExcludingIds(Builder $query, array $ids): Builder
    {
        return empty($ids) ? $query : $query->whereNotIn('id', $ids);
    }

    /**
     * Scope to exclude the given collection and all its transitive descendants.
     * Prevents circular hierarchies when selecting a parent collection.
     * Hard-caps traversal at 10 levels.
     *
     * @param  string  $collectionId  UUID of the collection whose descendants to exclude
     */
    public function scopeExcludingDescendantsOf(Builder $query, string $collectionId): Builder
    {
        $maxDepth = 10;
        $excludeIds = [$collectionId];
        $currentLevel = [$collectionId];

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            $nextLevel = static::whereIn('parent_id', $currentLevel)->pluck('id')->all();
            if (empty($nextLevel)) {
                break;
            }
            $excludeIds = array_merge($excludeIds, $nextLevel);
            $currentLevel = $nextLevel;

            if ($depth + 1 >= $maxDepth) {
                $hasMore = static::whereIn('parent_id', $currentLevel)->exists();
                if ($hasMore) {
                    throw new \RuntimeException('Collection hierarchy depth exceeds maximum of '.$maxDepth.' levels.');
                }
            }
        }

        return $query->whereNotIn('id', $excludeIds);
    }

    /**
     * Scope to exclude the given collection and all its transitive ancestors.
     * Prevents an ancestor from being set as a child of the collection.
     * Hard-caps traversal at 10 levels.
     *
     * @param  string  $collectionId  UUID of the collection whose ancestors to exclude
     */
    public function scopeExcludingAncestorsOf(Builder $query, string $collectionId): Builder
    {
        $maxDepth = 10;
        $excludeIds = [$collectionId];
        $currentId = $collectionId;

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            $parentId = static::where('id', $currentId)->value('parent_id');
            if ($parentId === null) {
                break;
            }
            $excludeIds[] = $parentId;
            $currentId = $parentId;

            if ($depth + 1 >= $maxDepth) {
                $hasMore = static::where('id', $currentId)->whereNotNull('parent_id')->exists();
                if ($hasMore) {
                    throw new \RuntimeException('Collection hierarchy depth exceeds maximum of '.$maxDepth.' levels.');
                }
            }
        }

        return $query->whereNotIn('id', $excludeIds);
    }
}
