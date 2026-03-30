<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contributor extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    protected $fillable = [
        'collection_id',
        'category',
        'display_order',
        'visible',
        'backward_compatibility',
        'internal_name',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'visible' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get a query builder scoped to this contributor's siblings (same collection_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('collection_id', $this->collection_id);
    }

    /**
     * Get the collection this contributor belongs to.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the translations for this contributor.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ContributorTranslation::class);
    }

    /**
     * Get the images for this contributor.
     */
    public function contributorImages(): HasMany
    {
        return $this->hasMany(ContributorImage::class)->orderBy('display_order');
    }
}
