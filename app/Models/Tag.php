<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'internal_name',
        'category',
        'language_id',
        'backward_compatibility',
        'description',
    ];

    /**
     * The items that belong to this tag.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->withTimestamps();
    }

    /**
     * Get the language this tag belongs to (optional).
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Scope to get tags for a specific item.
     *
     * @param  string|Item  $item  The item ID or Item model instance
     */
    public function scopeForItem(Builder $query, $item): Builder
    {
        $itemId = $item instanceof Item ? $item->id : $item;

        return $query->whereHas('items', function (Builder $query) use ($itemId) {
            $query->where('items.id', $itemId);
        });
    }

    /**
     * Scope to get tags by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get tags by language.
     */
    public function scopeByLanguage(Builder $query, string $languageId): Builder
    {
        return $query->where('language_id', $languageId);
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
