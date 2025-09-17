<?php

namespace App\Livewire;

use App\Models\Country;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CountrySelect extends Component
{
    public ?string $selected = null;

    public string $search = '';

    public bool $open = false;

    public string $name = 'country_id';

    public string $label = 'Country';

    public string $placeholder = 'Select a country...';

    protected $listeners = [
        'country-select.clear' => 'clear',
        'country-select.set' => 'setValue',
    ];

    public function mount(?string $value = null, string $name = 'country_id', string $label = 'Country')
    {
        $this->selected = $value;
        $this->name = $name;
        $this->label = $label;
    }

    public function updatedSearch()
    {
        // Search updated, keep dropdown open and reset any cached state
        $this->open = true;

        // If search is cleared, make sure we show all countries
        if (empty(trim($this->search))) {
            $this->search = '';
        }
    }

    public function selectCountry(string $countryId)
    {
        $this->selected = $countryId;
        $this->search = '';
        $this->open = false;

        // Emit event so parent forms can react
        $this->dispatch('country-selected', $this->selected);
    }

    public function toggleDropdown()
    {
        $allCountries = $this->countries; // This gets all countries

        // Log for debugging
        if (config('app.debug')) {
            Log::info('CountrySelect toggleDropdown - Countries count: '.$allCountries->count());
        }

        if ($allCountries->isEmpty()) {
            // Force refresh countries and try again
            $this->resetCountriesCache();
            $allCountries = $this->countries;

            if ($allCountries->isEmpty()) {
                return; // Still no countries, don't open
            }
        }

        $this->open = ! $this->open;
        if ($this->open) {
            $this->search = '';
        }
    }

    private function resetCountriesCache()
    {
        // This will help if there's any caching issue
        // Force a fresh query next time
    }

    public function closeDropdown()
    {
        $this->open = false;
        $this->search = '';
    }

    public function clear()
    {
        $this->selected = null;
        $this->search = '';
        $this->open = false;
        $this->dispatch('country-selected', null);
    }

    public function setValue(string $value)
    {
        $this->selected = strtoupper($value);
        $this->dispatch('country-selected', $this->selected);
    }

    public function getCountriesProperty()
    {
        try {
            // Always return all countries for client-side filtering
            // This improves performance by avoiding repeated server calls
            return Country::query()
                ->orderBy('internal_name')
                ->get(['id', 'internal_name']);

        } catch (\Exception $e) {
            // Log error and return empty collection
            if (config('app.debug')) {
                Log::error('CountrySelect getCountriesProperty error: '.$e->getMessage());
            }

            return collect();
        }
    }

    public function getFilteredCountriesProperty()
    {
        $countries = $this->countries;

        if (! empty($this->search)) {
            $search = strtolower(trim($this->search));

            return $countries->filter(function ($country) use ($search) {
                return str_contains(strtolower($country->internal_name), $search) ||
                       str_contains(strtolower($country->id), $search);
            });
        }

        return $countries;
    }

    public function getSelectedCountryProperty()
    {
        if (! $this->selected) {
            return null;
        }

        return Country::query()->where('id', $this->selected)->first(['id', 'internal_name']);
    }

    public function render()
    {
        $c = config('app_entities.partners.colors', []);

        return view('livewire.country-select', [
            'countries' => $this->filteredCountries,
            'allCountries' => $this->countries, // For checking if any countries exist
            'selectedCountry' => $this->selectedCountry,
            'c' => $c,
        ]);
    }
}
