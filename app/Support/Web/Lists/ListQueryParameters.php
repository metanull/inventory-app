<?php

namespace App\Support\Web\Lists;

final class ListQueryParameters
{
    public const SEARCH = 'q';

    public const SORT = 'sort';

    public const DIRECTION = 'direction';

    public const PAGE = 'page';

    public const PER_PAGE = 'per_page';

    public const ASC = 'asc';

    public const DESC = 'desc';

    /**
     * @return array<int, string>
     */
    public static function canonical(): array
    {
        return [
            self::SEARCH,
            self::SORT,
            self::DIRECTION,
            self::PAGE,
            self::PER_PAGE,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function directions(): array
    {
        return [self::ASC, self::DESC];
    }
}
