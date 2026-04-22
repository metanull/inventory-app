<?php

namespace App\Support\Web\Lists;

final class ListSortDefinition
{
    public function __construct(
        public readonly string $column,
        public readonly string $defaultDirection = ListQueryParameters::ASC,
    ) {}
}
