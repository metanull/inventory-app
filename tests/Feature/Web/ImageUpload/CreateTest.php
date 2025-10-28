<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ImageUpload;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class CreateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_create_form_renders(): void
    {
        $response = $this->get(route('images.upload'));
        $response->assertOk();
        $response->assertSee('Image Upload');
        $response->assertSee('Upload Images');
    }

    public function test_create_shows_file_input(): void
    {
        $response = $this->get(route('images.upload'));
        $response->assertOk();
        $response->assertSee('type="file"', false);
    }

    public function test_create_shows_information_about_processing(): void
    {
        $response = $this->get(route('images.upload'));
        $response->assertOk();
        $response->assertSee('About Image Processing');
        $response->assertSee('Available Images');
    }
}
