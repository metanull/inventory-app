<?php

namespace Tests\CiCd;

use Tests\TestCase;

class PhpExtensionsTest extends TestCase
{
    public function test_php_has_gd_extension(): void
    {
        $extension = 'gd';

        $this->assertTrue(
            extension_loaded($extension),
            "The PHP extension '{$extension}' is not installed or enabled."
        );
    }

    public function test_php_has_exif_extension(): void
    {
        $extension = 'exif';

        $this->assertTrue(
            extension_loaded($extension),
            "The PHP extension '{$extension}' is not installed or enabled."
        );
    }

    public function test_php_has_fileinfo_extension(): void
    {
        $extension = 'fileinfo';

        $this->assertTrue(
            extension_loaded($extension),
            "The PHP extension '{$extension}' is not installed or enabled."
        );
    }

    public function test_php_has_sqlite3_extension(): void
    {
        $extension = 'sqlite3';

        $this->assertTrue(
            extension_loaded($extension),
            "The PHP extension '{$extension}' is not installed or enabled."
        );
    }

    public function test_php_has_pdo_sqlite_extension(): void
    {
        $extension = 'pdo_sqlite';

        $this->assertTrue(
            extension_loaded($extension),
            "The PHP extension '{$extension}' is not installed or enabled."
        );
    }

    public function test_php_has_zip_extension(): void
    {
        $extension = 'zip';

        $this->assertTrue(
            extension_loaded($extension),
            "The PHP extension '{$extension}' is not installed or enabled."
        );
    }
}
