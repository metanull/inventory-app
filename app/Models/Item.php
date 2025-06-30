<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;
    use HasUuids;

    protected $with = [
        'partner',
        'country',
        'project',
    ];

    protected $fillable = [
        // 'id',
        'partner_id',
        'internal_name',
        'type',
        'backward_compatibility',
        'country_id',
        'project_id',
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
     * The tags that belong to this item.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_items');
    }

    /**
     * Get all contextualizations for this item.
     */
    public function contextualizations(): HasMany
    {
        return $this->hasMany(Contextualization::class);
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
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }
}
