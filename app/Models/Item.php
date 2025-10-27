<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use HasFactory;
    use HasUuids;

    // No model-level eager loads. Use request-scoped includes in controllers.

    // Type constants
    public const TYPE_OBJECT = 'object';

    public const TYPE_MONUMENT = 'monument';

    public const TYPE_DETAIL = 'detail';

    public const TYPE_PICTURE = 'picture';

    /**
     * Delete the model from the database.
     * Ensures atomic deletion of the Item, its translations,
     * and all spelling links from those translations.
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new \LogicException('No primary key defined on model.');
        }

        // If the model doesn't exist, nothing to delete
        if (! $this->exists) {
            return false;
        }

        // Fire the deleting event
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Perform atomic deletion in a transaction
        return DB::transaction(function () {
            // 1. For each translation, detach all spelling links
            foreach ($this->translations as $translation) {
                $translation->spellings()->detach();
            }

            // 2. Delete all translations
            $this->translations()->delete();

            // 3. Delete all item images
            $this->itemImages()->delete();

            // 4. Detach all many-to-many relationships
            $this->tags()->detach();
            $this->workshops()->detach();
            $this->attachedToCollections()->detach();

            // 6. Finally, delete the item itself
            $this->performDeleteOnModel();

            // Mark the model as non-existing
            $this->exists = false;

            // Fire the deleted event
            $this->fireModelEvent('deleted', false);

            return true;
        });
    }

    protected $fillable = [
        // 'id',
        'partner_id',
        'parent_id',
        'internal_name',
        'type',
        'backward_compatibility',
        'country_id',
        'project_id',
        'collection_id',
        'owner_reference',
        'mwnf_reference',
    ];

    /**
     * The partner owning or responsible for the Item.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * The country of the Item.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * The project associated with the Item.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The collection that contains this item.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * The parent item (for hierarchical relationships).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    /**
     * The child items (for hierarchical relationships).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id');
    }

    /**
     * The item images attached to this item.
     */
    public function itemImages(): HasMany
    {
        return $this->hasMany(ItemImage::class)->orderBy('display_order');
    }

    /**
     * The tags that belong to this item.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Artists associated with this item
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_item');
    }

    /**
     * Workshops associated with this item
     */
    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'item_workshop');
    }

    /**
     * Get all translations for this item.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ItemTranslation::class)->chaperone('item');
    }

    /**
     * Get all collections this item is attached to via many-to-many relationship.
     */
    public function attachedToCollections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_item')
            ->withTimestamps();
    }

    /**
     * Get the default context translation for this item in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?ItemTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this item.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?ItemTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?ItemTranslation
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
     * Scope to get only object items.
     */
    public function scopeObjects(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OBJECT);
    }

    /**
     * Scope to get only monument items.
     */
    public function scopeMonuments(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MONUMENT);
    }

    /**
     * Scope to get only detail items.
     */
    public function scopeDetails(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DETAIL);
    }

    /**
     * Scope to get only picture items.
     */
    public function scopePictures(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PICTURE);
    }

    /**
     * Scope to get parent items (items with no parent).
     */
    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get child items (items with a parent).
     */
    public function scopeChildren(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope to get items for a specific tag.
     *
     * @param  string|Tag  $tag  The tag ID or Tag model instance
     */
    public function scopeForTag(Builder $query, $tag): Builder
    {
        $tagId = $tag instanceof Tag ? $tag->id : $tag;

        return $query->whereHas('tags', function (Builder $query) use ($tagId) {
            $query->where('tags.id', $tagId);
        });
    }

    /**
     * Scope to get items that have ALL of the specified tags (AND condition).
     *
     * @param  array  $tags  Array of tag IDs or Tag model instances
     */
    public function scopeWithAllTags(Builder $query, array $tags): Builder
    {
        $tagIds = collect($tags)->map(function ($tag) {
            return $tag instanceof Tag ? $tag->id : $tag;
        })->toArray();

        foreach ($tagIds as $tagId) {
            $query->whereHas('tags', function (Builder $query) use ($tagId) {
                $query->where('tags.id', $tagId);
            });
        }

        return $query;
    }

    /**
     * Scope to get items that have ANY of the specified tags (OR condition).
     *
     * @param  array  $tags  Array of tag IDs or Tag model instances
     */
    public function scopeWithAnyTags(Builder $query, array $tags): Builder
    {
        $tagIds = collect($tags)->map(function ($tag) {
            return $tag instanceof Tag ? $tag->id : $tag;
        })->toArray();

        return $query->whereHas('tags', function (Builder $query) use ($tagIds) {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    /**
     * Reorder child items to eliminate gaps in display order.
     * Useful when child items are deleted or moved around.
     */
    public function reorderChildItems(): void
    {
        $this->getConnection()->transaction(function () {
            $childItems = $this->children()
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            foreach ($childItems as $index => $child) {
                // For now, we don't have display_order on items,
                // but this method structure is ready if we add it later
                // $child->update(['display_order' => $index + 1]);
            }
        });
    }

    /**
     * Reorder all item images to eliminate gaps in display order.
     * This is a convenience method to tighten image ordering.
     */
    public function reorderItemImages(): void
    {
        $this->getConnection()->transaction(function () {
            $images = $this->itemImages()
                ->orderBy('display_order')
                ->lockForUpdate()
                ->get();

            foreach ($images as $index => $image) {
                $image->update(['display_order' => $index + 1]);
            }
        });
    }

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    // Accessors and Mutators to ensure null values instead of empty strings
    public function getOwnerReferenceAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setOwnerReferenceAttribute($value): void
    {
        $this->attributes['owner_reference'] = $value === '' ? null : $value;
    }

    public function getMwnfReferenceAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setMwnfReferenceAttribute($value): void
    {
        $this->attributes['mwnf_reference'] = $value === '' ? null : $value;
    }
}
