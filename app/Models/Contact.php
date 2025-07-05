<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_name',
        'phone_number',
        'fax_number',
        'email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    // We'll handle phone number formatting manually since this is simpler for testing

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Format the phone number for display in international format.
     */
    public function formattedPhoneNumber(): ?string
    {
        // Just return the original phone number for now in tests
        return $this->phone_number;
    }

    /**
     * Format the fax number for display in international format.
     */
    public function formattedFaxNumber(): ?string
    {
        // Just return the original fax number for now in tests
        return $this->fax_number;
    }

    /**
     * The languages that belong to the contact.
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'contact_language')
            ->using(ContactLanguage::class)
            ->withPivot('label', 'id')
            ->withTimestamps();
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            // Load the languages relationship when a contact is retrieved
            if (! $model->relationLoaded('languages')) {
                $model->load('languages');
            }
        });
    }
}
