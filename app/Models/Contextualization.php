<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contextualization extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'context_id',
        'item_id',
        'detail_id',
        'extra',
        'internal_name',
        'backward_compatibility',
    ];

    protected $casts = [
        'extra' => 'array',
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
     * The context that this contextualization belongs to.
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * The item that this contextualization belongs to.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The detail that this contextualization belongs to.
     */
    public function detail(): BelongsTo
    {
        return $this->belongsTo(Detail::class);
    }

    /**
     * The internationalizations that belong to this contextualization.
     */
    public function internationalizations(): HasMany
    {
        return $this->hasMany(Internationalization::class);
    }

    /**
     * Scope to get contextualizations for the default context.
     */
    public function scopeDefault($query)
    {
        return $query->whereHas('context', function ($query) {
            $query->default();
        });
    }

    /**
     * Scope to get contextualizations for a specific context.
     */
    public function scopeForContext($query, Context $context)
    {
        return $query->where('context_id', $context->id);
    }

    /**
     * Scope to get contextualizations for items only.
     */
    public function scopeForItems($query)
    {
        return $query->whereNotNull('item_id');
    }

    /**
     * Scope to get contextualizations for details only.
     */
    public function scopeForDetails($query)
    {
        return $query->whereNotNull('detail_id');
    }

    /**
     * Create a contextualization with the default context.
     */
    public static function createWithDefaultContext(array $attributes)
    {
        $defaultContext = Context::default()->first();

        if (! $defaultContext) {
            throw new \Exception('No default context found');
        }

        $attributes['context_id'] = $defaultContext->id;

        return static::create($attributes);
    }
}
