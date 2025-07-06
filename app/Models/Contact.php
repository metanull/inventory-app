<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\PhoneNumber;

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
        if (! $this->phone_number) {
            return null;
        }
        try {
            return (new PhoneNumber($this->phone_number))->formatInternational();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format the fax number for display in international format.
     */
    public function formattedFaxNumber(): ?string
    {
        if (! $this->fax_number) {
            return null;
        }
        try {
            return (new PhoneNumber($this->fax_number))->formatInternational();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * The translations that belong to the contact.
     */
    public function translations()
    {
        return $this->hasMany(ContactTranslation::class);
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
            // Load the translations relationship when a contact is retrieved
            if (! $model->relationLoaded('translations')) {
                $model->load('translations');
            }
        });
    }
}
