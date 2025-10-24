<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PartnerImage extends Model
{
    use HasFactory, HasUuids;

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
     * Get the partner this image belongs to.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the next available display order for a partner.
     */
    public static function getNextDisplayOrderForPartner(string $partnerId): int
    {
        $maxOrder = static::where('partner_id', $partnerId)->max('display_order');

        return $maxOrder ? $maxOrder + 1 : 1;
    }

    /**
     * Move this image up in the display order.
     */
    public function moveUp(): bool
    {
        return $this->moveInDirection('up');
    }

    /**
     * Move this image down in the display order.
     */
    public function moveDown(): bool
    {
        return $this->moveInDirection('down');
    }

    /**
     * Move image in specified direction within a transaction.
     */
    protected function moveInDirection(string $direction): bool
    {
        return $this->getConnection()->transaction(function () use ($direction) {
            // Lock current image first to prevent race conditions
            $currentImage = static::where('id', $this->id)->lockForUpdate()->first();
            if (! $currentImage) {
                return false;
            }

            $currentOrder = $currentImage->display_order;

            if ($direction === 'up') {
                if ($currentOrder <= 1) {
                    return false;
                }

                $targetImage = static::where('partner_id', $this->partner_id)
                    ->where('display_order', $currentOrder - 1)
                    ->lockForUpdate()
                    ->first();
            } else { // down
                $targetImage = static::where('partner_id', $this->partner_id)
                    ->where('display_order', $currentOrder + 1)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $targetImage) {
                return false;
            }

            // Swap display orders
            $targetOrder = $targetImage->display_order;
            $targetImage->update(['display_order' => $currentOrder]);
            $currentImage->update(['display_order' => $targetOrder]);

            // Update this instance with the new order
            $this->display_order = $targetOrder;

            return true;
        });
    }

    /**
     * Tighten the display order for all images of a partner, eliminating gaps.
     */
    public function tightenOrderingForPartner(): void
    {
        $this->getConnection()->transaction(function () {
            $images = static::where('partner_id', $this->partner_id)
                ->orderBy('display_order')
                ->lockForUpdate()
                ->get();

            foreach ($images as $index => $image) {
                $newOrder = $index + 1;
                if ($image->display_order !== $newOrder) {
                    $image->update(['display_order' => $newOrder]);
                }
            }
        });
    }

    /**
     * Attach an available image to a partner, preserving the ID.
     */
    public static function attachFromAvailableImage(AvailableImage $availableImage, string $partnerId): static
    {
        return DB::transaction(function () use ($availableImage, $partnerId) {
            $displayOrder = static::getNextDisplayOrderForPartner($partnerId);

            $partnerImage = static::create([
                'id' => $availableImage->id, // Preserve the ID
                'partner_id' => $partnerId,
                'path' => $availableImage->path,
                'original_name' => $availableImage->original_name ?? '',
                'mime_type' => $availableImage->mime_type ?? '',
                'size' => $availableImage->size ?? 0,
                'alt_text' => $availableImage->comment,
                'display_order' => $displayOrder,
            ]);

            $availableImage->delete();

            return $partnerImage;
        });
    }

    /**
     * Detach this image and convert it back to an available image, preserving the ID.
     */
    public function detachToAvailableImage(): AvailableImage
    {
        return $this->getConnection()->transaction(function () {
            $availableImage = AvailableImage::create([
                'id' => $this->id, // Preserve the ID
                'path' => $this->path,
                'original_name' => $this->original_name,
                'mime_type' => $this->mime_type,
                'size' => $this->size,
                'comment' => $this->alt_text,
            ]);

            $this->delete();

            return $availableImage;
        });
    }

    /**
     * Override delete to handle reordering.
     */
    public function delete()
    {
        $result = parent::delete();

        // Tighten ordering after deletion to eliminate gaps
        if ($result) {
            // We need to get any remaining image from the same partner to call tightenOrderingForPartner
            $remainingImage = static::where('partner_id', $this->partner_id)->first();
            if ($remainingImage) {
                $remainingImage->tightenOrderingForPartner();
            }
        }

        return $result;
    }
}
