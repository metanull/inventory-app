<?php

namespace App\Support;

class FileSize
{
    public static function format(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2).' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 2).' KB';
        }

        return $bytes.' B';
    }
}
