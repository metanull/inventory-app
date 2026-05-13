<?php

namespace App\Support\AuthSnapshots;

final class AuthSnapshotFile
{
    public static function resolvePath(?string $path): string
    {
        $candidate = is_string($path) && $path !== ''
            ? $path
            : self::defaultPath();

        if (self::isAbsolutePath($candidate)) {
            return self::normalizePath($candidate);
        }

        return self::normalizePath(getcwd().DIRECTORY_SEPARATOR.$candidate);
    }

    public static function defaultPath(): string
    {
        return self::joinPath(
            sys_get_temp_dir(),
            'inventory-auth-snapshots',
            'auth-snapshot-'.now()->format('Ymd-His').'.json.enc',
        );
    }

    private static function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[A-Za-z]:[\\/]/', $path) === 1;
    }

    private static function joinPath(string $firstPart, string ...$parts): string
    {
        $path = rtrim($firstPart, '/\\');

        foreach ($parts as $part) {
            $path .= DIRECTORY_SEPARATOR.trim($part, '/\\');
        }

        return $path;
    }

    private static function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
