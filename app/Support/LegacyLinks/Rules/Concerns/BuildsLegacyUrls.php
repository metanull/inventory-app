<?php

namespace App\Support\LegacyLinks\Rules\Concerns;

use App\Models\Collection;
use App\Models\Country;
use App\Models\Project;

trait BuildsLegacyUrls
{
    protected function host(string $key): ?string
    {
        $host = config("legacy.links.public_hosts.{$key}");

        return is_string($host) && $host !== '' ? rtrim($host, '/') : null;
    }

    protected function projectHost(?string $project): ?string
    {
        if ($project === null || $project === '') {
            return null;
        }

        $host = config('legacy.links.public_hosts.projects.'.strtoupper($project));

        return is_string($host) && $host !== '' ? rtrim($host, '/') : null;
    }

    protected function backofficeUrl(string $section, string $edit): string
    {
        $host = config('legacy.links.backoffice_host');
        $host = is_string($host) && $host !== '' ? rtrim($host, '/') : 'https://virtual-office.museumwnf.org';

        return "{$host}/?section={$section}&edit={$edit}&";
    }

    protected function firstProjectCode(?Project $project): ?string
    {
        $backwardCompatibility = trim((string) $project?->backward_compatibility);

        if ($backwardCompatibility === '') {
            return null;
        }

        return strtoupper(trim(explode(',', $backwardCompatibility)[0]));
    }

    protected function legacyCountryCode(?Country $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $code = trim((string) ($country->backward_compatibility ?: $country->getKey()));

        return $code === '' ? null : strtolower($code);
    }

    protected function legacyCountryCodeForCollection(Collection $collection): ?string
    {
        $country = $this->legacyCountryCode($collection->country);

        if ($country !== null) {
            return $country;
        }

        $parentReference = $collection->parent?->backward_compatibility;
        $parts = $parentReference ? explode(':', $parentReference) : [];

        if (count($parts) === 3 && $parts[0] === 'mwnf3_explore' && $parts[1] === 'country') {
            return strtolower($parts[2]);
        }

        return null;
    }
}
