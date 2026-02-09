<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerLogo extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partner_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'logo_type',
        'alt_text',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'display_order' => 'integer',
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
     * Get the partner this logo belongs to.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the next available display order for a partner.
     *
     * @deprecated Use static::getNextDisplayOrderFor(['partner_id' => $partnerId]) instead
     */
    public static function getNextDisplayOrderForPartner(string $partnerId): int
    {
        return static::getNextDisplayOrderFor(['partner_id' => $partnerId]);
    }

    /**
     * Get a query builder scoped to this logo's siblings (same partner_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('partner_id', $this->partner_id);
    }

    /**
     * Scope a query to only include logos of a specific type.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('logo_type', $type);
    }

    /**
     * Scope a query to only include primary logos.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('logo_type', 'primary');
    }

    /**
     * Tighten ordering for all logos of this partner.
     */
    public function tightenOrderingForPartner(): void
    {
        $this->tightenOrdering();
    }
}
