<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Galleryable Pivot Model
 *
 * Represents the polymorphic many-to-many relationship between galleries
 * and their content (Items and Details).
 * Includes ordering information and timestamps.
 */
class Galleryable extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'galleryables';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gallery_id',
        'galleryable_id',
        'galleryable_type',
        'order',
        'backward_compatibility',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the gallery that owns the relationship.
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * Get the related model (Item or Detail).
     */
    public function galleryable(): MorphTo
    {
        return $this->morphTo();
    }
}
