<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemItemLink extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source_id',
        'target_id',
        'context_id',
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
     * The source item of the link (the item initiating the link).
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'source_id');
    }

    /**
     * The target item of the link (the item receiving the link).
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'target_id');
    }

    /**
     * The context in which the link exists.
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * Get all translations for this link.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ItemItemLinkTranslation::class);
    }

    /**
     * Scope to filter links by source item.
     *
     * @param  string|Item  $source  The source Item ID or Item model instance
     */
    public function scopeFromSource(Builder $query, $source): Builder
    {
        $sourceId = $source instanceof Item ? $source->id : $source;

        return $query->where('source_id', $sourceId);
    }

    /**
     * Scope to filter links by target item.
     *
     * @param  string|Item  $target  The target Item ID or Item model instance
     */
    public function scopeToTarget(Builder $query, $target): Builder
    {
        $targetId = $target instanceof Item ? $target->id : $target;

        return $query->where('target_id', $targetId);
    }

    /**
     * Scope to filter links by context.
     *
     * @param  string|Context  $context  The Context ID or Context model instance
     */
    public function scopeInContext(Builder $query, $context): Builder
    {
        $contextId = $context instanceof Context ? $context->id : $context;

        return $query->where('context_id', $contextId);
    }

    /**
     * Scope to filter links involving a specific item (as either source or target).
     *
     * @param  string|Item  $item  The Item ID or Item model instance
     */
    public function scopeInvolvingItem(Builder $query, $item): Builder
    {
        $itemId = $item instanceof Item ? $item->id : $item;

        return $query->where(function (Builder $query) use ($itemId) {
            $query->where('source_id', $itemId)
                ->orWhere('target_id', $itemId);
        });
    }
}
