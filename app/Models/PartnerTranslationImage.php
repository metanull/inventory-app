<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PartnerTranslationImage extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partner_translation_id',
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
     * Get the partner translation this image belongs to.
     */
    public function partnerTranslation(): BelongsTo
    {
        return $this->belongsTo(PartnerTranslation::class);
    }

    /**
     * Get the next available display order for a partner translation.
     */
    public static function getNextDisplayOrderForPartnerTranslation(string $partnerTranslationId): int
    {
        $maxOrder = static::where('partner_translation_id', $partnerTranslationId)->max('display_order');

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

                $targetImage = static::where('partner_translation_id', $this->partner_translation_id)
                    ->where('display_order', $currentOrder - 1)
                    ->lockForUpdate()
                    ->first();
            } else { // down
                $targetImage = static::where('partner_translation_id', $this->partner_translation_id)
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
     * Tighten the display order for all images of a partner translation, eliminating gaps.
     */
    public function tightenOrderingForPartnerTranslation(): void
    {
        $this->getConnection()->transaction(function () {
            $images = static::where('partner_translation_id', $this->partner_translation_id)
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
     * Attach an available image to a partner translation, preserving the ID.
     */
    public static function attachFromAvailableImage(AvailableImage $availableImage, string $partnerTranslationId, ?string $altText = null): static
    {
        return DB::transaction(function () use ($availableImage, $partnerTranslationId, $altText) {
            $displayOrder = static::getNextDisplayOrderForPartnerTranslation($partnerTranslationId);

            // Move file from available storage to pictures storage
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');

            $filename = $availableImage->path; // Already just filename

            // Move the file from images/ to pictures/
            \Illuminate\Support\Facades\Storage::disk($picturesDisk)->writeStream(
                $picturesDir.'/'.$filename,
                \Illuminate\Support\Facades\Storage::disk($availableDisk)->readStream($availableDir.'/'.$filename)
            );
            \Illuminate\Support\Facades\Storage::disk($availableDisk)->delete($availableDir.'/'.$filename);

            $partnerTranslationImage = static::create([
                'id' => $availableImage->id, // Preserve the ID
                'partner_translation_id' => $partnerTranslationId,
                'path' => $filename, // Keep filename unchanged
                'original_name' => $availableImage->original_name ?? '',
                'mime_type' => $availableImage->mime_type ?? '',
                'size' => $availableImage->size ?? 0,
                'alt_text' => $altText ?? $availableImage->comment,
                'display_order' => $displayOrder,
            ]);

            $availableImage->delete();

            return $partnerTranslationImage;
        });
    }

    /**
     * Detach this image and convert it back to an available image, preserving the ID.
     */
    public function detachToAvailableImage(): AvailableImage
    {
        return $this->getConnection()->transaction(function () {
            // Move file from pictures storage back to available storage
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');

            $filename = $this->path; // Already just filename

            // Move the file from pictures/ back to images/
            \Illuminate\Support\Facades\Storage::disk($availableDisk)->writeStream(
                $availableDir.'/'.$filename,
                \Illuminate\Support\Facades\Storage::disk($picturesDisk)->readStream($picturesDir.'/'.$filename)
            );
            \Illuminate\Support\Facades\Storage::disk($picturesDisk)->delete($picturesDir.'/'.$filename);

            $availableImage = AvailableImage::create([
                'id' => $this->id, // Preserve the ID
                'path' => $filename, // Keep filename unchanged
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
            // We need to get any remaining image from the same partner translation to call tightenOrderingForPartnerTranslation
            $remainingImage = static::where('partner_translation_id', $this->partner_translation_id)->first();
            if ($remainingImage) {
                $remainingImage->tightenOrderingForPartnerTranslation();
            }
        }

        return $result;
    }
}
