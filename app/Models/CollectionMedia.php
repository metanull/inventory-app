<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionMedia extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    protected $table = 'collection_media';

    protected $fillable = [
        'collection_id',
        'language_id',
        'type',
        'title',
        'description',
        'url',
        'display_order',
        'extra',
        'backward_compatibility',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'display_order' => 'integer',
        'extra' => 'object',
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
     * Get a query builder scoped to this media's siblings (same collection_id and type).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('collection_id', $this->collection_id)
            ->where('type', $this->type);
    }

    /**
     * Get the collection this media belongs to.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the language of this media.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
