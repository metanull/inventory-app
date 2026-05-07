<?php

namespace App\Support\LegacyLinks;

use Illuminate\Support\Collection;

final readonly class LegacyLinkSet
{
    /**
     * @param  array<int, LegacyLink>  $links
     */
    public function __construct(public array $links) {}

    /**
     * @return Collection<int, LegacyLink>
     */
    public function collect(): Collection
    {
        return collect($this->links);
    }

    public function hasResolvableLinks(): bool
    {
        return $this->collect()->contains(fn (LegacyLink $link): bool => $link->url !== null);
    }
}
